# Dokumentasi Integrasi Earscope → Website Laravel

> Sistem telemedicine berbasis AI untuk deteksi penyakit telinga menggunakan kamera endoskopi, model YOLO, dan web aplikasi Laravel.

---

## Daftar Isi

1. [Gambaran Umum](#gambaran-umum)
2. [Arsitektur Sistem](#arsitektur-sistem)
3. [Alur Data Lengkap](#alur-data-lengkap)
4. [Konfigurasi & Instalasi](#konfigurasi--instalasi)
5. [Endpoint API](#endpoint-api)
6. [File-File Kunci](#file-file-kunci)
7. [Troubleshooting](#troubleshooting)

---

## Gambaran Umum

Sistem ini terdiri dari dua komponen utama yang berjalan **di komputer yang sama** dan berkomunikasi melalui HTTP API lokal:

| Komponen | Teknologi | Port | Fungsi |
|----------|-----------|------|--------|
| **Earscope App** | Python + Flask + YOLOv8 | `5000` | Merekam video dari kamera endoskopi, menjalankan deteksi penyakit telinga dengan model AI, mengirim hasil ke Laravel |
| **Website** | Laravel + Vite | `8000` | Menerima data dari Flask, menyimpan video & hasil diagnosis, menampilkan ke dokter melalui halaman web |

---

## Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────┐
│                  Satu Komputer (127.0.0.1)              │
│                                                         │
│  ┌──────────────────────┐    HTTP POST     ┌──────────┐ │
│  │   Flask (port 5000)  │ ─────────────→  │  Laravel │ │
│  │                      │                 │(port 8000)│ │
│  │  - Buka kamera       │  /api/earscope/ │          │ │
│  │  - Rekam video       │  diagnosis-     │  - Terima│ │
│  │  - Deteksi YOLO      │  result         │    video │ │
│  │  - Kirim hasil       │                 │  - Simpan│ │
│  └──────────────────────┘                 │  - Polling│ │
│           ↑                               └──────────┘ │
│    Browser Dokter                               ↑       │
│    (buka :5000)                          Browser Dokter │
│    Input ID Konsultasi                   (buka :8000)   │
└─────────────────────────────────────────────────────────┘
```

---

## Alur Data Lengkap

### Tahap 1 — Persiapan Dokter

```
1. Dokter login ke website Laravel (http://127.0.0.1:8000)
2. Buka menu Diagnoses → lihat daftar konsultasi terjadwal
3. Klik [Add Diagnosis] pada konsultasi pasien
   └── Halaman otomatis POLLING ke /api/earscope/latest-result tiap 5 detik
   └── Catat ID Konsultasi yang muncul di banner biru (misal: ID = 7)
```

### Tahap 2 — Persiapan Earscope

```
4. Dokter buka Flask app di browser lain (http://127.0.0.1:5000)
5. Masukkan ID Konsultasi di field "ID Konsultasi" (misal: 7)
6. Klik [Start] untuk mulai merekam
   └── Kamera endoskopi menyala
   └── Video distream ke browser dalam format MJPEG
   └── Timer berjalan (maksimal 20 detik, bisa stop manual)
```

### Tahap 3 — Perekaman & Deteksi

```
7. Dokter arahkan kamera endoskopi ke telinga pasien
8. Klik [Stop] (atau otomatis berhenti di detik ke-20)
   └── Flask menghentikan stream
   └── Video raw disimpan lokal: videos/{timestamp}/raw_{timestamp}.webm
   └── Setiap frame diproses oleh model YOLOv8 (conf=0.3)
   └── Bounding box digambar → video processed disimpan: bbox_{timestamp}.webm
   └── Kelas yang paling sering terdeteksi = diagnosis akhir
```

### Tahap 4 — Pengiriman ke Laravel

```
9. Flask mengirim POST ke Laravel:
   POST http://127.0.0.1:8000/api/earscope/diagnosis-result
   Body:
     - consultation_id  : 7
     - hasil_diagnosis  : "earwaxplug"
     - raw_video        : [file .webm]
     - processed_video  : [file .webm]

10. Laravel menerima & menyimpan:
    - Video disimpan di storage/app/public/earscope_videos/{id}/raw/
    - Video disimpan di storage/app/public/earscope_videos/{id}/processed/
    - Record Diagnosis dibuat/diupdate di database
```

### Tahap 5 — Tampil di Website

```
11. Halaman dokter MENDETEKSI data baru (polling 5 detik)
    └── Badge: "Data diterima dari earscope" (hijau)
    └── Video hasil pemeriksaan ditampilkan otomatis
    └── Field diagnosis_result terisi dengan hasil AI

12. Dokter review → edit jika perlu → klik [Submit Diagnosis]
    └── Status konsultasi berubah menjadi DONE
    └── Pasien bisa melihat hasilnya di portal pasien
```

---

## Konfigurasi & Instalasi

### Flask (earscope-model)

**File: `earscope-model/.env`**

```env
DEBUG=true
HOST=0.0.0.0
PORT=5000

APP_KEY=bebas_isi_teks_apa_saja

# URL Laravel API — gunakan 127.0.0.1 jika Flask & Laravel di komputer yang sama
API_VIDEO_URL=http://127.0.0.1:8000/api/earscope/diagnosis-result
```

> [!IMPORTANT]
> **Jika Flask dan Laravel di komputer berbeda (jaringan):**
> Ganti `127.0.0.1` dengan IP address komputer yang menjalankan Laravel.
> Jalankan `ipconfig` → cari **IPv4 Address** di bagian **Wi-Fi**.
> Contoh: `API_VIDEO_URL=http://192.168.1.10:8000/api/earscope/diagnosis-result`
>
> Dan jalankan Laravel dengan: `php artisan serve --host=0.0.0.0 --port=8000`

**Menjalankan Flask:**

```powershell
cd earscope-model
pip install -r requirements.txt
python app.py
```

---

### Laravel (web)

**File: `web/.env` (bagian yang relevan)**

```env
APP_URL=http://127.0.0.1:8000
```

> [!WARNING]
> `APP_URL` harus diisi dengan port yang benar (`8000`), bukan `http://localhost`.
> Ini mempengaruhi URL video yang dikirim ke browser.

**Menjalankan Laravel:**

```powershell
cd web
php artisan migrate
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8000
npm run dev
```

---

## Endpoint API

### `POST /api/earscope/diagnosis-result`

Dipanggil oleh Flask setelah stop recording.

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| `consultation_id` | integer | ID konsultasi dari tabel `consultation_requests` |
| `hasil_diagnosis` | string | Label kelas hasil deteksi (misal: `earwaxplug`) |
| `raw_video` | file | Video mentah (WebM/MP4, maks 200MB) |
| `processed_video` | file | Video dengan bounding box hasil YOLO |

**Response sukses (201):**
```json
{
  "success": true,
  "message": "Data earscope berhasil diterima.",
  "diagnosis_id": 1
}
```

**Response gagal (422):**
```json
{
  "success": false,
  "message": "Konsultasi tidak ditemukan atau statusnya bukan scheduled."
}
```

---

### `GET /api/earscope/latest-result?consultation_id={id}`

Di-polling oleh halaman diagnosa dokter setiap 5 detik.

**Response jika data ada (200):**
```json
{
  "success": true,
  "ai_result": "earwaxplug",
  "raw_video_url": "http://127.0.0.1:8000/storage/earscope_videos/7/raw/xxx.webm",
  "processed_video_url": "http://127.0.0.1:8000/storage/earscope_videos/7/processed/xxx.webm"
}
```

**Response jika belum ada data (404):**
```json
{
  "success": false,
  "message": "Belum ada data earscope."
}
```

> [!NOTE]
> Response 404 dari endpoint ini adalah **normal** — artinya data belum dikirim dari Flask.
> Halaman dokter akan terus polling sampai mendapat 200.

---

## File-File Kunci

### Flask (earscope-model)

| File | Fungsi |
|------|--------|
| `app.py` | Aplikasi Flask utama: routing, recording, deteksi YOLO, pengiriman API |
| `config.py` | Membaca konfigurasi dari `.env` |
| `.env` | Variabel lingkungan: port, API URL |
| `templates/index.html` | UI dokter untuk kamera (start/stop, input consultation_id) |
| `model-earscope/best.pt` | Model YOLOv8 yang sudah dilatih untuk penyakit telinga |
| `model-earscope/data.yml` | Label kelas dan warna bounding box |
| `videos/` | Folder penyimpanan video lokal sementara |

### Laravel (web)

| File | Fungsi |
|------|--------|
| `routes/web.php` | Mendaftarkan route API earscope (tanpa middleware auth) |
| `app/Http/Controllers/Api/EarscopeApiController.php` | Controller: menerima data Flask & polling endpoint |
| `app/Http/Controllers/DiagnosisController.php` | Controller: form diagnosis dokter |
| `app/Models/Diagnosis.php` | Model dengan kolom `ai_result`, `raw_video_path`, `processed_video_path` |
| `database/migrations/2026_06_05_000001_add_earscope_fields_to_diagnoses_table.php` | Migrasi kolom earscope |
| `resources/views/doctor/diagnoses.blade.php` | Halaman diagnosa: polling, tampil video, form submit |
| `bootstrap/app.php` | Konfigurasi middleware: exclude CSRF untuk `api/earscope/*` |
| `storage/app/public/earscope_videos/` | Penyimpanan video permanen terorganisir per consultation_id |

---

## Label Penyakit (Kelas Model YOLO)

| ID | Label | Keterangan |
|----|-------|------------|
| `0` | `acute otitis media` | Otitis media akut |
| `1` | `chronic otitis media` | Otitis media kronis |
| `2` | `earwaxplug` | Sumbatan serumen |
| `3` | `myringosclerosis` | Miringosklerosis |
| `-1` | `Unknown` | Tidak ada deteksi (confidence < 0.3) |

---

## Troubleshooting

### Video tidak bisa diputar di browser

**Gejala:** Video muncul di halaman tapi tidak bisa diplay, atau elemen `<video>` tidak muncul.

**Penyebab & Solusi:**

| Penyebab | Solusi |
|----------|--------|
| Codec `mp4v` tidak didukung browser | ✅ Sudah diganti ke `VP80` (WebM) |
| `storage:link` belum dijalankan | Jalankan `php artisan storage:link` |
| File video kosong (0 bytes) | Cek kamera terdeteksi di log Flask |

---

### Diagnosis selalu "Unknown"

**Gejala:** Data berhasil dikirim ke Laravel tapi `ai_result = "Unknown"`.

**Penyebab & Solusi:**

| Penyebab | Solusi |
|----------|--------|
| Kamera tidak menangkap gambar jelas | Pastikan kamera mengarah ke objek |
| Confidence threshold terlalu tinggi | ✅ Sudah diturunkan ke `0.3` |
| Model tidak dilatih dengan data tersebut | Periksa `model-earscope/best.pt` |
| Kamera tidak terbuka (index salah) | Flask akan auto-detect index 0→1→2 |

Cek log Flask saat recording:
```
Processing detection on 400 frames
Frame 12: detected class 2 (earwaxplug) conf=0.45
Total detections: 187 from 400 frames
```
Jika `Total detections: 0` → kamera/model bermasalah.

---

### Data tidak terkirim ke Laravel (video ada di folder tapi tidak muncul di web)

**Gejala:** Folder `earscope-model/videos/` bertambah file tapi database Laravel kosong.

**Penyebab & Solusi:**

| Penyebab | Solusi |
|----------|--------|
| `API_VIDEO_URL` salah (pakai IP WiFi, bukan 127.0.0.1) | Ganti ke `http://127.0.0.1:8000/...` jika 1 komputer |
| `network_available = False` | Restart Flask setelah fix `.env` |
| Laravel tidak listen di IP yang dituju | Jalankan dengan `--host=0.0.0.0` jika beda komputer |
| Consultation status bukan `scheduled` | Pastikan status konsultasi = `scheduled` di database |

---

### Polling terus 404

**Gejala:** Console browser menampilkan `GET /api/earscope/latest-result 404` terus-menerus.

**Penjelasan:** 404 dari endpoint ini adalah **normal** jika Flask belum mengirim data. Polling akan otomatis berhenti ketika data diterima (response 200).

Jika 404 muncul bahkan setelah Flask selesai mengirim:
1. Cek tabel `diagnoses` di database: `php artisan tinker` → `App\Models\Diagnosis::all()`
2. Pastikan `consultation_id` yang diinput di Flask sama dengan yang ada di URL halaman dokter
3. Cek log Laravel di `storage/logs/laravel.log`

---

*Dokumentasi ini dibuat untuk keperluan Tugas Akhir — Sistem Telemedicine Earscope*
