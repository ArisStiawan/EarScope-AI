# EarScope-AI: Sistem Diagnosis Kesehatan Telinga (YOLO + Laravel + Flask)

Selamat datang di repositori **EarScope-AI**! Repositori ini berisi sistem lengkap diagnosis kesehatan telinga luar dan tengah. Sistem ini mengintegrasikan model kecerdasan buatan **YOLOv8** (Object Detection) untuk mendeteksi kelainan atau penyakit telinga secara real-time menggunakan kamera otoscope USB.

Project ini terdiri dari dua komponen utama:

1. **`web/`** - Aplikasi web utama untuk manajemen dokter, pasien, konsultasi, dan rekam medis (menggunakan framework **Laravel 11 / PHP** & **Bootstrap/Tailwind**).
2. **`earscope-model/`** - Server deteksi AI dan streaming kamera otoscope secara real-time (menggunakan **Flask / Python** & **OpenCV** & **YOLOv8**).

---

## 📂 Struktur Repositori

```text
FOLDER_TA/
├── web/                  # Aplikasi Laravel (Web Backend & Frontend)
├── earscope-model/       # Aplikasi Flask (AI & Camera Streamer)
└── README.md             # Dokumentasi Panduan Setup (File ini)
```

---

## 🛠️ Prasyarat Sistem (Prerequisites)

Sebelum menjalankan aplikasi, pastikan komputer Anda sudah terinstall:

- **PHP** >= 8.2 (Disarankan menggunakan Laragon atau XAMPP)
- **Composer** (Dependency manager PHP)
- **Node.js** & **NPM** (Untuk compile frontend asset)
- **MySQL Database**
- **Python** >= 3.8 & <= 3.11 (Disarankan Python 3.10)
- **Kamera Otoscope USB** (Atau webcam laptop sebagai alternatif pengujian)

---

## 🚀 Panduan Setup & Instalasi

Ikuti langkah-langkah di bawah ini secara berurutan untuk menjalankan sistem di komputer lokal Anda:

### Langkah 1: Setup Database MySQL

1. Pastikan server database MySQL Anda (Laragon/XAMPP) sudah aktif.
2. Buat database kosong baru bernama **`coba_ta`** (atau sesuaikan dengan kebutuhan Anda).

---

### Langkah 2: Setup Aplikasi Web (Laravel)

Buka terminal baru, lalu masuk ke folder `web/`:

```bash
cd web
```

1. **Salin file konfigurasi environment:**
   ```bash
   copy .env.example .env
   ```
2. **Install dependency PHP (Composer):**
   ```bash
   composer install
   ```
3. **Install dependency JavaScript (NPM):**
   ```bash
   npm install
   ```
4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```
5. **Sesuaikan koneksi database di file `web/.env`:**
   Buka file `.env` di dalam folder `web/`, cari bagian database dan sesuaikan dengan milik Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=coba_ta
   DB_USERNAME=root
   DB_PASSWORD=
   ```
6. **Jalankan migrasi database dan seeder data dummy:**
   ```bash
   php artisan migrate --seed
   ```
7. **Buat symbolic link storage (SANGAT PENTING):**
   Langkah ini wajib dilakukan agar foto hasil tangkapan kamera dan video rekaman dari Flask dapat diakses oleh website Laravel:
   ```bash
   php artisan storage:link
   ```

---

### Langkah 3: Setup Aplikasi AI & Kamera (Flask)

Buka terminal baru lagi (pisahkan dengan terminal Laravel), lalu masuk ke folder `earscope-model/`:

```bash
cd earscope-model
```

1. **Buat Virtual Environment Python (venv):**
   ```bash
   python -m venv venv
   ```
2. **Aktifkan Virtual Environment:**
   - **Windows (PowerShell):**
     ```powershell
     venv\Scripts\activate
     ```
   - **Linux / macOS:**
     ```bash
     source venv/bin/activate
     ```
3. **Install semua library Python yang dibutuhkan:**
   ```bash
   pip install -r requirements.txt
   ```
4. **Salin file konfigurasi environment Flask:**
   ```bash
   copy .env.example .env
   ```
   _(Opsional: Buka file `.env` di `earscope-model/` untuk menyesuaikan port default `5000` dan URL API Laravel)._

---

### Langkah 4: Hubungkan Laravel dengan Flask

Agar Laravel dapat menyalakan server Flask secara otomatis dari website, Anda perlu melengkapi konfigurasi path di file **`web/.env`**.

Buka file **`web/.env`** Anda, lalu cari atau tambahkan baris berikut di bagian paling bawah:

```env
# Flask Earscope Configuration
# PENTING: Gunakan forward slash (/) meskipun di Windows agar tidak terjadi error parse dotenv!
FLASK_APP_PATH="C:/path/to/FOLDER_TA/earscope-model"
FLASK_PYTHON_PATH="C:/path/to/FOLDER_TA/earscope-model/venv/Scripts/python.exe"
FLASK_URL=http://127.0.0.1:5000
```

> ⚠️ **Catatan Penting:**
> Ganti `"C:/path/to/FOLDER_TA/"` dengan absolute path folder tempat Anda menyimpan project ini di komputer Anda. Pastikan tanda garis miring menggunakan **forward slash (`/`)**.

---

## 🏃‍♂️ Cara Menjalankan Aplikasi

Setelah semua langkah instalasi selesai, jalankan perintah berikut:

### 1. Jalankan Server Web Laravel

Di folder `web/`, buka 2 terminal berbeda (atau jalankan di background):

- **Terminal 1 (Server PHP Laravel):**
  ```bash
  php artisan serve
  ```
- **Terminal 2 (Compiler Asset CSS/Vite):**
  ```bash
  npm run dev
  ```
  Buka browser dan akses halaman web di: **`http://127.0.0.1:8000`**

### 2. Jalankan Server Flask (AI Kamera)

Anda memiliki **2 Opsi** untuk menjalankan server Flask:

- **Opsi A (Otomatis - Direkomendasikan):**
  Anda tidak perlu menjalankan Flask secara manual. Saat Dokter mengklik tombol **"Mulai Earscope"** di halaman diagnosis Laravel, sistem Laravel akan otomatis mendeteksi dan menyalakan Flask di background komputer Anda.
- **Opsi B (Manual):**
  Jika ingin menyalakannya secara manual untuk keperluan debugging, buka terminal baru di folder `earscope-model/`, aktifkan `venv`, lalu jalankan:
  ```bash
  python app.py
  ```

---

## 🩺 Alur Penggunaan Aplikasi (Workflow)

1. **Registrasi/Login:** Masuk sebagai **Dokter** di website Laravel.
2. **Permintaan Konsultasi:** Pasien melakukan permintaan pemeriksaan.
3. **Set Schedule:** Dokter menyetujui permintaan dan menjadwalkan pemeriksaan.
4. **Halaman Diagnosis:**
   - Masuk ke daftar konsultasi yang sudah dijadwalkan, lalu klik **Diagnosa**.
   - Klik tombol **Mulai Earscope** untuk menyalakan kamera.
   - Di dalam interface kamera, Anda dapat melihat stream video dengan kotak deteksi YOLO secara real-time.
   - Klik tombol **Ambil Foto** untuk menangkap gambar telinga (sistem akan menyimpan versi _raw_ asli dan versi _bounding box_ hasil deteksi YOLO).
   - Klik **Mulai Merekam** untuk merekam video pemeriksaan telinga.
   - Hasil foto dan video akan otomatis terunggah dan muncul di galeri hasil pemeriksaan pada halaman web Laravel.
5. **Verifikasi:** Dokter mengisi catatan diagnosa medis final dan memverifikasi hasilnya.
