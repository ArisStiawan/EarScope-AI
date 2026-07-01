import os
import cv2
import yaml
from flask import Flask, request, render_template, Response, jsonify, abort
from ultralytics import YOLO
from config import Config
import requests
import datetime
from collections import Counter
from threading import Event
import logging
import threading
import queue
import socket
import time
import numpy as np

# Configure logging
logging.basicConfig(level=logging.INFO, 
                    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)


# ============================================================
# GLOBAL STATE
# ============================================================

network_available = True
stop_event = Event()
send_queue = queue.Queue()

# Shared camera state
latest_frame       = None
latest_frame_lock  = threading.Lock()
camera_cap         = None
camera_lock        = threading.Lock()
camera_thread_running = False
camera_thread_lock = threading.Lock()
current_camera_index = None

# Recording state
is_recording          = False
recorded_frames       = []
recorded_frames_lock  = threading.Lock()

recording_data = {
    "raw_path":       None,
    "bbox_path":      None,
    "diagnosis":      None,
    "consultation_id": ""
}

app = Flask(__name__)
app.config.from_object(Config)

app.secret_key = app.config['APP_KEY']

if not app.secret_key:
    raise ValueError("APP_KEY is not set! Please define it in your .env file.")

# Load labels and colors from YAML file
with open('model-earscope/data.yml', 'r') as f:
    data = yaml.safe_load(f)
    labels = data['labels']
    colors = data['colors']


# ============================================================
# NETWORK MONITOR
# ============================================================

def check_internet(timeout=3):
    from config import Config
    targets = []
    try:
        url = Config.API_VIDEO_URL
        if url and url.startswith('http'):
            from urllib.parse import urlparse
            parsed = urlparse(url)
            host = parsed.hostname or '127.0.0.1'
            port = parsed.port or 8000
            targets.append((host, port))
    except Exception:
        pass
    targets.append(('8.8.8.8', 53))
    for host, port in targets:
        try:
            socket.setdefaulttimeout(timeout)
            s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            s.connect((host, port))
            s.close()
            return True
        except socket.error:
            continue
    return False

def network_monitor():
    global network_available
    while True:
        connected = check_internet()
        if connected != network_available:
            network_available = connected
            logger.info(f"Network status changed: {'Available' if connected else 'Not available'}")
        time.sleep(10)

threading.Thread(target=network_monitor, daemon=True).start()


# ============================================================
# YOLO DETECTION
# ============================================================

class Detection:
    def __init__(self):
        self.model = YOLO(r"model-earscope/best.pt")

    def predict(self, img, classes=[], conf=0.5):
        if classes:
            results = self.model.predict(img, classes=classes, conf=conf)
        else:
            results = self.model.predict(img, conf=conf)
        return results

    def predict_and_detect(self, img, classes=[], conf=0.5, rectangle_thickness=2, text_thickness=1):
        results = self.predict(img, classes, conf=conf)
        for result in results:
            for box in result.boxes:
                class_id = int(box.cls[0])
                color = colors.get(class_id, [255, 255, 255])
                cv2.rectangle(img, (int(box.xyxy[0][0]), int(box.xyxy[0][1])),
                              (int(box.xyxy[0][2]), int(box.xyxy[0][3])), color, rectangle_thickness)
                label = labels.get(class_id, "Unknown")
                cv2.putText(img, f"{label}",
                            (int(box.xyxy[0][0]), int(box.xyxy[0][1]) - 10),
                            cv2.FONT_HERSHEY_PLAIN, 1, color, text_thickness)
        return img, results

    def detect_from_image(self, image):
        result_img, _ = self.predict_and_detect(image, classes=[], conf=0.5)
        return result_img

detection = Detection()


# ============================================================
# CAMERA WORKER THREAD (Single shared instance)
# ============================================================

def camera_worker(camera_index=None):
    """
    Background thread that opens the camera once and continuously
    reads frames into latest_frame. Also writes frames to recorded_frames
    when is_recording is True.
    """
    global camera_cap, camera_thread_running, latest_frame, is_recording, recorded_frames

    backend = cv2.CAP_DSHOW if os.name == 'nt' else cv2.CAP_ANY
    cap = None

    # Try manual index first
    if camera_index is not None and camera_index != 'auto':
        try:
            cam_idx = int(camera_index)
            logger.info(f"Mencoba kamera manual index {cam_idx}...")
            test_cap = cv2.VideoCapture(cam_idx, backend)
            if test_cap.isOpened():
                ret, _ = test_cap.read()
                if ret:
                    cap = test_cap
                    logger.info(f"Kamera manual ditemukan di index {cam_idx}")
                else:
                    test_cap.release()
            else:
                test_cap.release()
        except Exception as e:
            logger.error(f"Error membuka kamera manual: {e}")

    # Auto-detect fallback
    if cap is None:
        skip_idx = None
        if camera_index is not None and camera_index != 'auto':
            try:
                skip_idx = int(camera_index)
            except ValueError:
                pass
        for cam_idx in [0, 1, 2]:
            if cam_idx == skip_idx:
                continue
            logger.info(f"Mencoba kamera index {cam_idx}...")
            test_cap = cv2.VideoCapture(cam_idx, backend)
            if test_cap.isOpened():
                ret, _ = test_cap.read()
                if ret:
                    cap = test_cap
                    logger.info(f"Kamera ditemukan di index {cam_idx}")
                    break
            test_cap.release()

    if cap is None:
        logger.error("Tidak ada kamera yang terdeteksi!")
        with camera_thread_lock:
            camera_thread_running = False
        return

    width, height = 1280, 720
    cap.set(cv2.CAP_PROP_FRAME_WIDTH, width)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, height)

    with camera_lock:
        camera_cap = cap

    logger.info("Camera worker aktif, streaming preview...")

    while True:
        with camera_thread_lock:
            if not camera_thread_running:
                break

        ret, frame = cap.read()
        if not ret:
            logger.warning("Gagal membaca frame dari kamera.")
            break

        frame = cv2.resize(frame, (width, height))

        # Update shared latest frame
        with latest_frame_lock:
            latest_frame = frame.copy()

        # If recording, store frame
        if is_recording:
            with recorded_frames_lock:
                recorded_frames.append(frame.copy())

    # Cleanup
    cap.release()
    with camera_lock:
        camera_cap = None
    with latest_frame_lock:
        latest_frame = None
    with camera_thread_lock:
        camera_thread_running = False
    logger.info("Camera worker berhenti.")


def ensure_camera_started(camera_index=None):
    """Start camera worker thread if not already running, or restart if index changed."""
    global camera_thread_running, current_camera_index
    
    need_restart = False
    with camera_thread_lock:
        if camera_thread_running:
            if camera_index is not None and str(camera_index) != str(current_camera_index):
                need_restart = True
                camera_thread_running = False

    if need_restart:
        logger.info(f"Menghentikan kamera saat ini ({current_camera_index}) untuk ganti ke {camera_index}...")
        while True:
            with camera_thread_lock:
                if not camera_thread_running:
                    break
            time.sleep(0.1)

    with camera_thread_lock:
        if not camera_thread_running:
            current_camera_index = camera_index
            camera_thread_running = True
            t = threading.Thread(target=camera_worker, args=(camera_index,), daemon=True)
            t.start()
            logger.info(f"Camera worker thread dimulai dengan index {camera_index}.")


def generate_preview():
    """MJPEG generator — streams latest_frame regardless of recording state."""
    # Waiting frame shown before camera is ready
    waiting_frame = np.zeros((720, 1280, 3), dtype=np.uint8)
    cv2.putText(waiting_frame, "Menghubungkan ke kamera endoskopi...", (200, 350),
                cv2.FONT_HERSHEY_SIMPLEX, 1.0, (180, 180, 180), 2)

    while True:
        with latest_frame_lock:
            frame = latest_frame.copy() if latest_frame is not None else None

        display = frame if frame is not None else waiting_frame

        ret, buffer = cv2.imencode('.jpg', display, [cv2.IMWRITE_JPEG_QUALITY, 80])
        if ret:
            yield (b'--frame\r\n'
                   b'Content-Type: image/jpeg\r\n\r\n' + buffer.tobytes() + b'\r\n')

        time.sleep(0.033)  # ~30fps


# ============================================================
# PROCESS & SEND (Post-recording pipeline)
# ============================================================

def process_and_send(frames, consultation_id):
    """Run YOLO on recorded frames, write videos, enqueue for API send."""
    logger.info(f"Processing {len(frames)} frames for consultation_id={consultation_id}")

    if not frames:
        logger.warning("Tidak ada frame yang direkam. Skip processing.")
        return

    timestamp  = datetime.datetime.now().strftime("%Y%m%d_%H%M%S")
    folder     = f"videos/{timestamp}"
    os.makedirs(folder, exist_ok=True)

    width, height = 1280, 720

    # --- Write raw video ---
    raw_path  = os.path.join(folder, f"raw_{timestamp}.mp4")
    fourcc    = cv2.VideoWriter_fourcc(*'mp4v')
    raw_writer = cv2.VideoWriter(raw_path, fourcc, 20.0, (width, height))

    for frame in frames:
        raw_writer.write(frame)
    raw_writer.release()

    # --- Run YOLO and write bbox video ---
    bbox_path  = os.path.join(folder, f"bbox_{timestamp}.mp4")
    bbox_fourcc = cv2.VideoWriter_fourcc(*'mp4v')
    bbox_writer = cv2.VideoWriter(bbox_path, bbox_fourcc, 20.0, (width, height))

    detected_classes = []
    for i, frame in enumerate(frames):
        resized = cv2.resize(frame.copy(), (640, 640))
        result_img, results = detection.predict_and_detect(resized, conf=0.3)
        result_img = cv2.resize(result_img, (width, height))
        for result in results:
            for box in result.boxes:
                class_id   = int(box.cls[0])
                conf_score = float(box.conf[0])
                detected_classes.append(class_id)
                logger.info(f"Frame {i}: {labels.get(class_id,'?')} conf={conf_score:.2f}")
        bbox_writer.write(result_img)

    bbox_writer.release()

    logger.info(f"Total deteksi: {len(detected_classes)} dari {len(frames)} frames")
    logger.info(f"Distribusi kelas: {Counter(detected_classes).most_common()}")

    diagnosis_id    = Counter(detected_classes).most_common(1)[0][0] if detected_classes else -1
    diagnosis_label = labels.get(diagnosis_id, "Unknown")

    record_data = {
        "raw_path":       raw_path,
        "bbox_path":      bbox_path,
        "diagnosis":      diagnosis_label,
        "consultation_id": consultation_id
    }
    send_queue.put(record_data)
    logger.info("Data rekaman diantrekan untuk pengiriman ke API.")


# Thread worker untuk kirim data ke API secara async
def api_sender_worker():
    while True:
        record_data = send_queue.get()
        try:
            success = send_to_api(
                record_data["raw_path"],
                record_data["bbox_path"],
                record_data["diagnosis"],
                record_data.get("consultation_id", "")
            )
            logger.info(f"Send to API selesai, success={success}")
        except Exception as e:
            logger.error(f"Exception in api_sender_worker: {e}")
        send_queue.task_done()

threading.Thread(target=api_sender_worker, daemon=True).start()


# ============================================================
# ROUTES
# ============================================================

@app.route('/health')
def health():
    return jsonify({'status': 'ok', 'message': 'Flask earscope is running'}), 200


@app.route('/')
def index():
    consultation_id = request.args.get('consultation_id', '')
    patient_name    = request.args.get('patient_name', '')
    return render_template('index.html',
                           consultation_id=consultation_id,
                           patient_name=patient_name)


@app.route('/preview')
def preview():
    """
    Stream live camera preview (no recording).
    Automatically starts the shared camera worker if not running.
    """
    camera_index = request.args.get('camera_index', None)
    ensure_camera_started(camera_index)
    return Response(generate_preview(), mimetype='multipart/x-mixed-replace; boundary=frame')


@app.route('/start_recording', methods=['POST'])
def start_recording():
    """Begin recording frames to memory. Camera must already be streaming."""
    global is_recording, recorded_frames, recording_data

    if not network_available:
        return jsonify({'status': 'error', 'message': 'Tidak ada koneksi internet.'}), 503

    body            = request.get_json(silent=True) or {}
    consultation_id = body.get('consultation_id', '')

    if not consultation_id:
        return jsonify({'status': 'error', 'message': 'consultation_id diperlukan.'}), 400

    if latest_frame is None:
        return jsonify({'status': 'error', 'message': 'Kamera belum aktif. Tunggu preview muncul.'}), 400

    recording_data = {
        "raw_path":       None,
        "bbox_path":      None,
        "diagnosis":      None,
        "consultation_id": consultation_id
    }
    stop_event.clear()

    with recorded_frames_lock:
        recorded_frames = []

    is_recording = True
    logger.info(f"[start_recording] Mulai merekam untuk consultation_id={consultation_id}")

    return jsonify({'status': 'success', 'message': 'Recording dimulai.'})


@app.route('/stop_recording', methods=['POST'])
def stop_recording():
    """Stop recording and trigger YOLO processing + API send in background."""
    global is_recording, recorded_frames

    if not network_available:
        return jsonify({'status': 'error', 'message': 'Tidak ada koneksi internet.'}), 503

    body            = request.get_json(silent=True) or {}
    consultation_id = body.get('consultation_id', recording_data.get('consultation_id', ''))
    recording_data['consultation_id'] = consultation_id
    logger.info(f"[stop_recording] consultation_id={consultation_id}")

    is_recording = False
    stop_event.set()

    # Grab frames and clear buffer
    with recorded_frames_lock:
        frames_to_process = recorded_frames.copy()
        recorded_frames   = []

    logger.info(f"[stop_recording] {len(frames_to_process)} frames diambil untuk diproses.")

    # Process in background so we don't block the HTTP response
    threading.Thread(target=process_and_send, args=(frames_to_process, consultation_id), daemon=True).start()

    return jsonify({'status': 'stopping', 'message': 'Recording stopped, processing data'})


@app.route('/capture_photo', methods=['POST'])
def capture_photo():
    """
    Capture the latest frame from the live preview, run YOLO, and send to Laravel.
    Works during both preview and recording modes.
    """
    global latest_frame

    with latest_frame_lock:
        frame = latest_frame.copy() if latest_frame is not None else None

    if frame is None:
        return jsonify({'status': 'error', 'message': 'Kamera belum aktif atau belum ada frame.'}), 400

    body            = request.get_json(silent=True) or {}
    consultation_id = body.get('consultation_id', recording_data.get('consultation_id', ''))

    if not consultation_id:
        return jsonify({'status': 'error', 'message': 'consultation_id diperlukan.'}), 400

    logger.info(f"[capture_photo] Mengambil foto untuk consultation_id={consultation_id}")

    timestamp      = datetime.datetime.now().strftime("%Y%m%d_%H%M%S_%f")
    capture_folder = f"videos/captures/{consultation_id}"
    os.makedirs(capture_folder, exist_ok=True)

    raw_filename = f"raw_{timestamp}.jpg"
    raw_path     = os.path.join(capture_folder, raw_filename)
    cv2.imwrite(raw_path, frame)
    logger.info(f"[capture_photo] Raw photo disimpan: {raw_path}")

    resized       = cv2.resize(frame.copy(), (640, 640))
    bbox_frame, results = detection.predict_and_detect(resized, conf=0.3)
    bbox_frame    = cv2.resize(bbox_frame, (frame.shape[1], frame.shape[0]))

    detected_labels = []
    for result in results:
        for box in result.boxes:
            class_id   = int(box.cls[0])
            conf_score = float(box.conf[0])
            detected_labels.append(f"{labels.get(class_id, 'Unknown')} ({conf_score:.0%})")

    bbox_filename = f"bbox_{timestamp}.jpg"
    bbox_path     = os.path.join(capture_folder, bbox_filename)
    cv2.imwrite(bbox_path, bbox_frame)
    logger.info(f"[capture_photo] Bbox photo disimpan: {bbox_path}")

    ai_result = ', '.join(detected_labels) if detected_labels else 'Tidak terdeteksi'
    try:
        api_url = app.config['API_VIDEO_URL'].replace('/diagnosis-result', '/upload-photo')
        logger.info(f"[capture_photo] Mengirim ke {api_url}")

        with open(raw_path, 'rb') as raw_file, open(bbox_path, 'rb') as bbox_file:
            files = {
                'raw_image':  (raw_filename,  raw_file,  'image/jpeg'),
                'bbox_image': (bbox_filename, bbox_file, 'image/jpeg'),
            }
            post_data = {
                'consultation_id':    consultation_id,
                'ai_screening_result': ai_result,
            }
            response = requests.post(
                api_url,
                files=files,
                data=post_data,
                timeout=15,
                headers={'Accept': 'application/json'},
            )

        logger.info(f"[capture_photo] API Response: {response.status_code}")
        if response.ok:
            return jsonify({'status': 'success', 'message': 'Foto berhasil diambil dan dikirim.', 'detections': detected_labels})
        else:
            logger.error(f"[capture_photo] API error: {response.text}")
            return jsonify({'status': 'warning', 'message': 'Foto berhasil diambil tapi gagal dikirim ke server.', 'detections': detected_labels}), 207

    except Exception as e:
        logger.error(f"[capture_photo] Exception: {e}")
        return jsonify({'status': 'warning', 'message': f'Foto tersimpan lokal tapi gagal dikirim: {str(e)}', 'detections': detected_labels}), 207


def _mime_for_video(path):
    """Deteksi MIME type video berdasarkan ekstensi file."""
    ext = os.path.splitext(path)[1].lower()
    return {
        '.webm': 'video/webm',
        '.mp4':  'video/mp4',
        '.avi':  'video/x-msvideo',
        '.mov':  'video/quicktime',
        '.mkv':  'video/x-matroska',
    }.get(ext, 'video/mp4')


def send_to_api(raw_path, bbox_path, diagnosis, consultation_id=''):
    url = app.config['API_VIDEO_URL']
    logger.info(f"Mengirim data ke API: {url}")
    logger.info(f"Raw: {raw_path} | Bbox: {bbox_path} | Diagnosis: {diagnosis} | ID: {consultation_id}")
    try:
        raw_mime  = _mime_for_video(raw_path)
        bbox_mime = _mime_for_video(bbox_path)
        logger.info(f"MIME → raw={raw_mime}, bbox={bbox_mime}")
        with open(raw_path, 'rb') as raw, open(bbox_path, 'rb') as bbox:
            files = {
                'raw_video':       (os.path.basename(raw_path),  raw,  raw_mime),
                'processed_video': (os.path.basename(bbox_path), bbox, bbox_mime)
            }
            data = {
                'hasil_diagnosis': diagnosis,
                'consultation_id': consultation_id
            }
            response = requests.post(
                url,
                files=files,
                data=data,
                headers={'Accept': 'application/json'},
            )

        logger.info(f"API Status: {response.status_code}")
        try:
            logger.info(f"API JSON: {response.json()}")
        except Exception:
            logger.info(f"API Text: {response.text}")

        return 200 <= response.status_code < 300

    except Exception as e:
        logger.error(f"Error send_to_api: {e}")
        return False


if __name__ == '__main__':
    app.run(host=app.config['HOST'],
            port=app.config['PORT'],
            debug=app.config['DEBUG'])