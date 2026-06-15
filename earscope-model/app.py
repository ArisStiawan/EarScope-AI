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


# Global variables 

network_available = True  # Default True, monitor akan update jika benar-benar tidak bisa kirim
stop_event = Event()
send_queue = queue.Queue()
latest_frame = None        # Menyimpan frame terakhir dari kamera (untuk capture foto)
latest_frame_lock = threading.Lock()  # Thread-safe access

recording_data = {
    "raw_path": None,
    "bbox_path": None,
    "diagnosis": None
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

def check_internet(timeout=3):
    """
    Cek koneksi ke Laravel server (prioritas) atau DNS Google (fallback).
    Return True jika setidaknya salah satu bisa dicapai.
    """
    from config import Config
    targets = []

    # Parse host:port dari API_VIDEO_URL
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

    # Fallback ke Google DNS
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
        time.sleep(10)  # cek tiap 10 detik

# Start thread network monitor saat app start
threading.Thread(target=network_monitor, daemon=True).start()

class Detection:
    def __init__(self):
        # Load the YOLO model
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
                # Get the class and color
                class_id = int(box.cls[0])
                color = colors.get(class_id, [255, 255, 255])  # Default to white if class not found

                # Draw bounding box with the assigned color
                cv2.rectangle(img, (int(box.xyxy[0][0]), int(box.xyxy[0][1])),
                              (int(box.xyxy[0][2]), int(box.xyxy[0][3])), color, rectangle_thickness)

                # Draw label text
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
# ROUTES
# ============================================================

@app.route('/health')
def health():
    """Health check endpoint — Laravel memanggil ini untuk cek apakah Flask sudah running."""
    return jsonify({'status': 'ok', 'message': 'Flask earscope is running'}), 200


@app.route('/')
def index():
    consultation_id = request.args.get('consultation_id', '')
    patient_name = request.args.get('patient_name', '')
    return render_template('index.html',
                           consultation_id=consultation_id,
                           patient_name=patient_name)


@app.route('/process_video')
def process_video():
    if not network_available:
        logger.warning("Tidak bisa mulai recording: jaringan tidak tersedia")
        return jsonify({'status': 'error', 'message': 'Tidak ada koneksi internet. Coba lagi nanti.'}), 503
    global recording_data

    # Ambil consultation_id dari query string
    consultation_id = request.args.get('consultation_id', '')
    logger.info(f"[process_video] consultation_id={consultation_id}")

    # Reset recording data
    recording_data = {
        "raw_path": None,
        "bbox_path": None,
        "diagnosis": None,
        "consultation_id": consultation_id
    }
    stop_event.clear()
    logger.info("Starting video processing and recording")
    return Response(record_and_stream(consultation_id), mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/stop_recording', methods=['POST'])
def stop_recording():
    if not network_available:
        logger.warning("Tidak bisa stop recording: jaringan tidak tersedia")
        return jsonify({'status': 'error', 'message': 'Tidak ada koneksi internet. Stop recording tidak tersedia.'}), 503

    # Baca consultation_id dari JSON body (dikirim oleh browser)
    body = request.get_json(silent=True) or {}
    consultation_id = body.get('consultation_id', recording_data.get('consultation_id', ''))
    recording_data['consultation_id'] = consultation_id
    logger.info(f"[stop_recording] consultation_id={consultation_id}")

    logger.info("Received request to stop recording")
    stop_event.set()

    return jsonify({'status': 'stopping', 'message': 'Recording stopped, processing data'})


@app.route('/capture_photo', methods=['POST'])
def capture_photo():
    """
    Menangkap frame terakhir dari kamera, menjalankan deteksi YOLO,
    dan mengirim foto raw + foto bbox ke Laravel API.
    """
    global latest_frame

    with latest_frame_lock:
        frame = latest_frame.copy() if latest_frame is not None else None

    if frame is None:
        return jsonify({'status': 'error', 'message': 'Kamera belum aktif atau belum ada frame.'}), 400

    body = request.get_json(silent=True) or {}
    consultation_id = body.get('consultation_id', recording_data.get('consultation_id', ''))

    if not consultation_id:
        return jsonify({'status': 'error', 'message': 'consultation_id diperlukan.'}), 400

    logger.info(f"[capture_photo] Capturing photo for consultation_id={consultation_id}")

    # --- Simpan foto raw ---
    timestamp = datetime.datetime.now().strftime("%Y%m%d_%H%M%S_%f")
    capture_folder = f"videos/captures/{consultation_id}"
    os.makedirs(capture_folder, exist_ok=True)

    raw_filename = f"raw_{timestamp}.jpg"
    raw_path = os.path.join(capture_folder, raw_filename)
    cv2.imwrite(raw_path, frame)
    logger.info(f"[capture_photo] Raw photo saved: {raw_path}")

    # --- Jalankan deteksi YOLO pada frame ---
    resized = cv2.resize(frame.copy(), (640, 640))
    bbox_frame, results = detection.predict_and_detect(resized, conf=0.3)
    bbox_frame = cv2.resize(bbox_frame, (frame.shape[1], frame.shape[0]))

    # Ambil label deteksi
    detected_labels = []
    for result in results:
        for box in result.boxes:
            class_id = int(box.cls[0])
            conf_score = float(box.conf[0])
            detected_labels.append(f"{labels.get(class_id, 'Unknown')} ({conf_score:.0%})")

    bbox_filename = f"bbox_{timestamp}.jpg"
    bbox_path = os.path.join(capture_folder, bbox_filename)
    cv2.imwrite(bbox_path, bbox_frame)
    logger.info(f"[capture_photo] Bbox photo saved: {bbox_path}")

    # --- Kirim ke Laravel API ---
    ai_result = ', '.join(detected_labels) if detected_labels else 'Tidak terdeteksi'
    try:
        api_url = app.config['API_VIDEO_URL'].replace('/diagnosis-result', '/upload-photo')
        logger.info(f"[capture_photo] Sending photos to {api_url}")

        with open(raw_path, 'rb') as raw_file, open(bbox_path, 'rb') as bbox_file:
            files = {
                'raw_image': (raw_filename, raw_file, 'image/jpeg'),
                'bbox_image': (bbox_filename, bbox_file, 'image/jpeg'),
            }
            data = {
                'consultation_id': consultation_id,
                'ai_screening_result': ai_result,
            }
            response = requests.post(api_url, files=files, data=data, timeout=15)

        logger.info(f"[capture_photo] API Response: {response.status_code}")
        if response.ok:
            return jsonify({
                'status': 'success',
                'message': 'Foto berhasil diambil dan dikirim.',
                'detections': detected_labels,
            })
        else:
            logger.error(f"[capture_photo] API error: {response.text}")
            return jsonify({
                'status': 'warning',
                'message': 'Foto berhasil diambil tapi gagal dikirim ke server.',
                'detections': detected_labels,
            }), 207

    except Exception as e:
        logger.error(f"[capture_photo] Exception: {e}")
        return jsonify({
            'status': 'warning',
            'message': f'Foto tersimpan lokal tapi gagal dikirim: {str(e)}',
            'detections': detected_labels,
        }), 207


# Thread worker untuk kirim data ke API secara async
def api_sender_worker():
    while True:
        record_data = send_queue.get()  # Tunggu data rekaman masuk queue
        try:
            success = send_to_api(
                record_data["raw_path"],
                record_data["bbox_path"],
                record_data["diagnosis"],
                record_data.get("consultation_id", "")
            )
            logger.info(f"Send to API finished with success={success}")
        except Exception as e:
            logger.error(f"Exception in sending to API: {e}")
        send_queue.task_done()


# Jalankan worker thread sekali saat app mulai
threading.Thread(target=api_sender_worker, daemon=True).start()

def record_and_stream(consultation_id=''):
    global recording_data, latest_frame
    logger.info(f"Starting recording and streaming process (consultation_id={consultation_id})")

    # --- Auto-detect kamera: coba index 0, 1, 2 ---
    cap = None
    for cam_idx in [0, 1, 2]:
        logger.info(f"Mencoba kamera index {cam_idx}...")
        test_cap = cv2.VideoCapture(cam_idx, cv2.CAP_DSHOW)
        if test_cap.isOpened():
            ret, _ = test_cap.read()
            if ret:
                cap = test_cap
                logger.info(f"Kamera ditemukan di index {cam_idx}")
                break
        test_cap.release()

    if cap is None or not cap.isOpened():
        logger.error("Tidak ada kamera yang terdeteksi!")
        # Kirim frame error agar browser tahu kamera gagal
        err_frame = np.zeros((480, 640, 3), dtype=np.uint8)
        cv2.putText(err_frame, "KAMERA TIDAK TERDETEKSI", (60, 220),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 0, 255), 2)
        cv2.putText(err_frame, "Pastikan kamera terhubung", (100, 270),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.7, (200, 200, 200), 1)
        _, buf = cv2.imencode('.jpg', err_frame)
        for _ in range(30):  # tampilkan pesan selama ~1.5 detik
            yield (b'--frame\r\nContent-Type: image/jpeg\r\n\r\n' + buf.tobytes() + b'\r\n')
        return

    width, height = 1280, 720
    cap.set(cv2.CAP_PROP_FRAME_WIDTH, width)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, height)

    timestamp = datetime.datetime.now().strftime("%Y%m%d_%H%M%S")
    folder = f"videos/{timestamp}"
    os.makedirs(folder, exist_ok=True)

    raw_path = os.path.join(folder, f"raw_{timestamp}.webm")
    bbox_path = os.path.join(folder, f"bbox_{timestamp}.webm")

    fourcc = cv2.VideoWriter_fourcc(*'VP80')  # WebM VP8 - didukung semua browser
    raw_writer = cv2.VideoWriter(raw_path, fourcc, 20.0, (width, height))
    if not raw_writer.isOpened():
        logger.error("Gagal membuat VideoWriter! Coba fallback ke mp4v")
        raw_path = os.path.join(folder, f"raw_{timestamp}.mp4")
        fourcc = cv2.VideoWriter_fourcc(*'mp4v')
        raw_writer = cv2.VideoWriter(raw_path, fourcc, 20.0, (width, height))

    frames = []

    try:
        while cap.isOpened() and not stop_event.is_set():
            ret, frame = cap.read()
            if not ret:
                logger.warning("Failed to read frame from camera")
                break

            frame = cv2.resize(frame, (width, height))
            raw_writer.write(frame)
            frames.append(frame)

            # Simpan frame terakhir untuk fitur capture foto
            with latest_frame_lock:
                latest_frame = frame.copy()

            # Tampilkan frame mentah saja (tanpa bounding box)
            ret, buffer = cv2.imencode('.jpg', frame)
            frame_bytes = buffer.tobytes()
            yield (b'--frame\r\n'
                   b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n')

    except Exception as e:
        logger.error(f"Error during recording: {e}")
    finally:
        logger.info("Releasing video resources")
        cap.release()
        raw_writer.release()

        # Reset latest_frame saat kamera berhenti
        with latest_frame_lock:
            latest_frame = None

        # Proses deteksi setelah selesai merekam
        logger.info(f"Processing detection on {len(frames)} recorded frames")

        # Sesuaikan ekstensi bbox dengan raw (keduanya webm atau mp4)
        raw_ext = os.path.splitext(raw_path)[1]
        bbox_path = os.path.splitext(bbox_path)[0] + raw_ext

        bbox_fourcc = cv2.VideoWriter_fourcc(*'VP80') if raw_ext == '.webm' else cv2.VideoWriter_fourcc(*'mp4v')
        bbox_writer = cv2.VideoWriter(bbox_path, bbox_fourcc, 20.0, (width, height))
        detected_classes = []

        for i, frame in enumerate(frames):
            resized_frame = cv2.resize(frame.copy(), (640, 640))  # input ke model
            result_img, results = detection.predict_and_detect(resized_frame, conf=0.3)  # threshold lebih rendah

            result_img = cv2.resize(result_img, (width, height))

            for result in results:
                for box in result.boxes:
                    class_id = int(box.cls[0])
                    conf_score = float(box.conf[0])
                    detected_classes.append(class_id)
                    logger.info(f"Frame {i}: detected class {class_id} ({labels.get(class_id, '?')}) conf={conf_score:.2f}")
            bbox_writer.write(result_img)

        bbox_writer.release()

        logger.info(f"Total detections: {len(detected_classes)} from {len(frames)} frames")
        logger.info(f"Class distribution: {Counter(detected_classes).most_common()}")

        diagnosis_id = Counter(detected_classes).most_common(1)[0][0] if detected_classes else -1
        diagnosis_label = labels.get(diagnosis_id, "Unknown")

        record_data = {
            "raw_path": raw_path,
            "bbox_path": bbox_path,
            "diagnosis": diagnosis_label,
            "consultation_id": consultation_id or recording_data.get('consultation_id', '')
        }
        send_queue.put(record_data)
        logger.info("Recording data enqueued for sending to API")

def send_to_api(raw_path, bbox_path, diagnosis, consultation_id=''):
    """Send recorded videos and diagnosis to Laravel API"""
    url = app.config['API_VIDEO_URL']

    logger.info(f"Sending data to API at {url}")
    logger.info(f"Raw Video: {raw_path}")
    logger.info(f"Processed Video: {bbox_path}")
    logger.info(f"Diagnosis: {diagnosis}")
    logger.info(f"Consultation ID: {consultation_id}")

    try:
        with open(raw_path, 'rb') as raw, open(bbox_path, 'rb') as bbox:
            files = {
                'raw_video': (os.path.basename(raw_path), raw, 'video/mp4'),
                'processed_video': (os.path.basename(bbox_path), bbox, 'video/mp4')
            }

            data = {
                'hasil_diagnosis': diagnosis,
                'consultation_id': consultation_id
            }

            response = requests.post(url, files=files, data=data)

        logger.info(f"API Response Status Code: {response.status_code}")

        try:
            response_data = response.json()
            logger.info(f"API JSON Response: {response_data}")
        except Exception as e:
            logger.error(f"Failed to parse JSON response: {e}")
            logger.info(f"Raw Text Response: {response.text}")

        return 200 <= response.status_code < 300

    except Exception as e:
        logger.error(f"Error sending to API: {e}")
        return False


if __name__ == '__main__':
    app.run(host=app.config['HOST'],
            port=app.config['PORT'],
            debug=app.config['DEBUG'])