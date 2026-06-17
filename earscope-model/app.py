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
import platform

IS_WINDOWS = platform.system() == 'Windows'

def get_video_capture(idx):
    if IS_WINDOWS:
        return cv2.VideoCapture(idx, cv2.CAP_DSHOW)
    return cv2.VideoCapture(idx)

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

class CameraManager:
    def __init__(self):
        self.cap = None
        self.camera_idx = -1
        self.lock = threading.Lock()
        self.is_recording = False
        self.recorded_frames = []
        self.consultation_id = ""
        self.width = 1280
        self.height = 720

    def set_camera(self, idx):
        with self.lock:
            if self.camera_idx == idx and self.cap is not None and self.cap.isOpened():
                return True
            
            # Close old camera if open
            if self.cap is not None:
                logger.info(f"Releasing camera {self.camera_idx} to switch to {idx}")
                self.cap.release()
                self.cap = None
                
            logger.info(f"Opening camera index {idx}...")
            self.cap = get_video_capture(idx)
            if not self.cap.isOpened():
                logger.error(f"Failed to open camera index {idx}")
                self.camera_idx = -1
                return False
                
            self.cap.set(cv2.CAP_PROP_FRAME_WIDTH, self.width)
            self.cap.set(cv2.CAP_PROP_FRAME_HEIGHT, self.height)
            self.camera_idx = idx
            return True

    def get_frame(self):
        with self.lock:
            if self.cap is None or not self.cap.isOpened():
                return None
            ret, frame = self.cap.read()
            if not ret:
                return None
            frame = cv2.resize(frame, (self.width, self.height))
            
            # If recording, append a copy of the frame
            if self.is_recording:
                self.recorded_frames.append(frame.copy())
                
            return frame

    def start_recording(self, consultation_id):
        with self.lock:
            self.recorded_frames = []
            self.consultation_id = consultation_id
            self.is_recording = True
            logger.info(f"Started recording frames for consultation_id={consultation_id}")

    def stop_recording(self):
        with self.lock:
            if not self.is_recording:
                return None, ""
            self.is_recording = False
            frames = list(self.recorded_frames)
            self.recorded_frames = []
            logger.info(f"Stopped recording. Total frames captured: {len(frames)}")
            return frames, self.consultation_id

camera_manager = CameraManager()

def gen_frames(camera_idx):
    global latest_frame
    
    # Try setting the camera
    success = camera_manager.set_camera(camera_idx)
    if not success:
        # Fallback to auto-detect
        for alt_idx in [0, 1, 2, 3]:
            if alt_idx == camera_idx:
                continue
            if camera_manager.set_camera(alt_idx):
                break
                
    while True:
        frame = camera_manager.get_frame()
        if frame is None:
            # Yield error frame
            err_frame = np.zeros((480, 640, 3), dtype=np.uint8)
            cv2.putText(err_frame, "KAMERA TIDAK TERDETEKSI", (60, 220),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 0, 255), 2)
            _, buf = cv2.imencode('.jpg', err_frame)
            yield (b'--frame\r\nContent-Type: image/jpeg\r\n\r\n' + buf.tobytes() + b'\r\n')
            time.sleep(0.5)
            continue
            
        with latest_frame_lock:
            latest_frame = frame.copy()
            
        ret, buffer = cv2.imencode('.jpg', frame)
        if not ret:
            continue
        yield (b'--frame\r\nContent-Type: image/jpeg\r\n\r\n' + buffer.tobytes() + b'\r\n')
        # Limit frame rate slightly to avoid high CPU usage
        time.sleep(0.04)

# ============================================================
# ROUTES
# ============================================================

@app.route('/health')
def health():
    """Health check endpoint — Laravel memanggil ini untuk cek apakah Flask sudah running."""
    return jsonify({'status': 'ok', 'message': 'Flask earscope is running'}), 200


@app.route('/get_cameras')
def get_cameras():
    """Detect available camera indices."""
    available_cameras = []
    # Test indices 0 to 5
    for cam_idx in range(6):
        cap = get_video_capture(cam_idx)
        if cap.isOpened():
            ret, _ = cap.read()
            if ret:
                available_cameras.append(cam_idx)
            cap.release()
    # Fallback to at least camera 0 if none detected
    if not available_cameras:
        available_cameras = [0]
    return jsonify({'cameras': available_cameras})


@app.route('/')
def index():
    consultation_id = request.args.get('consultation_id', '')
    patient_name = request.args.get('patient_name', '')
    return render_template('index.html',
                           consultation_id=consultation_id,
                           patient_name=patient_name)


@app.route('/video_feed')
@app.route('/process_video')
def video_feed_route():
    camera_id = request.args.get('camera_id', 0, type=int)
    return Response(gen_frames(camera_id), mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/start_recording', methods=['POST'])
def start_recording_route():
    body = request.get_json(silent=True) or {}
    consultation_id = body.get('consultation_id', '')
    if not consultation_id:
        return jsonify({'status': 'error', 'message': 'consultation_id is required.'}), 400
        
    camera_manager.start_recording(consultation_id)
    return jsonify({'status': 'success', 'message': 'Recording started.'})

@app.route('/stop_recording', methods=['POST'])
def stop_recording_route():
    frames, consultation_id = camera_manager.stop_recording()
    if frames:
        threading.Thread(target=process_recorded_video_async, args=(frames, consultation_id), daemon=True).start()
        return jsonify({'status': 'stopping', 'message': 'Recording stopped, processing in background.'})
    return jsonify({'status': 'error', 'message': 'No active recording or no frames captured.'}), 400


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
    consultation_id = body.get('consultation_id', camera_manager.consultation_id)

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

def process_recorded_video_async(frames, consultation_id):
    if not frames:
        logger.error("No frames to process!")
        return
        
    timestamp = datetime.datetime.now().strftime("%Y%m%d_%H%M%S")
    folder = f"videos/{timestamp}"
    os.makedirs(folder, exist_ok=True)

    raw_path = os.path.join(folder, f"raw_{timestamp}.webm")
    bbox_path = os.path.join(folder, f"bbox_{timestamp}.webm")
    
    width, height = camera_manager.width, camera_manager.height
    fourcc = cv2.VideoWriter_fourcc(*'VP80')
    
    # Save raw video
    logger.info(f"Saving raw video to {raw_path}")
    raw_writer = cv2.VideoWriter(raw_path, fourcc, 20.0, (width, height))
    for frame in frames:
        raw_writer.write(frame)
    raw_writer.release()
    
    # Run YOLO detection & Save processed video
    logger.info(f"Running YOLO on {len(frames)} frames...")
    bbox_writer = cv2.VideoWriter(bbox_path, fourcc, 20.0, (width, height))
    detected_classes = []
    
    for i, frame in enumerate(frames):
        resized_frame = cv2.resize(frame.copy(), (640, 640))
        result_img, results = detection.predict_and_detect(resized_frame, conf=0.3)
        result_img = cv2.resize(result_img, (width, height))
        
        for result in results:
            for box in result.boxes:
                class_id = int(box.cls[0])
                detected_classes.append(class_id)
                
        bbox_writer.write(result_img)
    bbox_writer.release()
    
    # Determine diagnosis
    diagnosis_id = Counter(detected_classes).most_common(1)[0][0] if detected_classes else -1
    diagnosis_label = labels.get(diagnosis_id, "Unknown")
    
    # Put in send queue to Laravel
    record_data = {
        "raw_path": raw_path,
        "bbox_path": bbox_path,
        "diagnosis": diagnosis_label,
        "consultation_id": consultation_id
    }
    send_queue.put(record_data)
    logger.info(f"Background processing done. Diagnosis: {diagnosis_label}. Enqueued for sending.")

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