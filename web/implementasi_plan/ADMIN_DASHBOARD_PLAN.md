# Rencana Implementasi Admin Dashboard - EarScope

## 1. OVERVIEW
Admin Dashboard yang menampilkan:
- **Statistik Utama**: Total dokter aktif, total pasien terdaftar, total konsultasi (per bulan)
- **Activity Log**: Tabel menampilkan aktivitas sistem (user baru, dokter login, dokter upload hasil)

---

## 2. DATABASE STRUCTURE

### 2.1 Migration - ActivityLog Table
**File**: `database/migrations/XXXX_XX_XX_XXXXXX_create_activity_logs_table.php`

Struktur tabel:
```
- id (primary key)
- user_id (foreign key to users)
- activity_type (enum: user_registered, doctor_login, consultation_uploaded, etc)
- description (text)
- data (json - optional, untuk detail tambahan)
- ip_address (nullable)
- user_agent (nullable)
- created_at
- updated_at
```

**Activity Types yang akan dicatat**:
1. `user_registered` - User/Pasien/Dokter baru registrasi
2. `doctor_login` - Dokter login ke sistem
3. `consultation_uploaded` - Dokter mengunggah hasil konsultasi
4. `consultation_requested` - Pasien membuat konsultasi baru
5. `consultation_approved` - Dokter menyetujui konsultasi
6. `consultation_rejected` - Dokter menolak konsultasi

---

## 3. MODELS

### 3.1 ActivityLog Model
**File**: `app/Models/ActivityLog.php`

```php
class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'activity_type', 'description', 'data', 'ip_address', 'user_agent'];
    protected $casts = ['data' => 'array'];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
```

### 3.2 Relasi pada Diagnosis Model
Tambahkan event listener untuk mencatat ketika dokter upload hasil konsultasi.

---

## 4. BACKEND IMPLEMENTATION

### 4.1 Controller - AdminDashboardController
**File**: `app/Http/Controllers/AdminDashboardController.php`

**Methods yang akan dibuat**:

#### a) `index()`
Menampilkan halaman admin dashboard dengan:
- Total dokter aktif
- Total pasien terdaftar
- Statistik konsultasi per bulan (6 bulan terakhir)
- Activity log terbaru (20-50 entri)

#### b) `getTotalActiveDoctors()`
```php
// Return: count(Doctor) where user.status = active
// Query: Doctor::count() atau Doctor::with('user')->get()
```

#### c) `getTotalPatients()`
```php
// Return: count(Patient)
```

#### d) `getConsultationStats()`
```php
// Return: array konsultasi per bulan (6 bulan terakhir)
// Query: Group by MONTH(created_at) dari ConsultationRequest
```

#### e) `getActivityLogs($limit = 50)`
```php
// Return: Latest activity logs dengan user info
// Query: ActivityLog::with('user')->latest()->limit($limit)
```

---

## 5. EVENT LISTENERS & TRACKING

### 5.1 Event untuk mencatat aktivitas
Perlu ditambahkan di:
- **User Registration** (Auth\RegisteredUserController) → ActivityLog: `user_registered`
- **Doctor Login** (LoginController atau middleware) → ActivityLog: `doctor_login`
- **Diagnosis Created** (DoctorController atau Model Observer) → ActivityLog: `consultation_uploaded`
- **Consultation Request Created** (PatientController) → ActivityLog: `consultation_requested`
- **Consultation Approved** (DoctorController) → ActivityLog: `consultation_approved`
- **Consultation Rejected** (DoctorController) → ActivityLog: `consultation_rejected`

### 5.2 Activity Logger Helper Class
**File**: `app/Helpers/ActivityLogger.php`

```php
class ActivityLogger {
    public static function log($userId, $type, $description, $data = null)
    {
        ActivityLog::create([
            'user_id' => $userId,
            'activity_type' => $type,
            'description' => $description,
            'data' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
```

---

## 6. FRONTEND - BLADE TEMPLATE

### 6.1 View File
**File**: `resources/views/admin/dashboard.blade.php`

**Layout Structure**:

```
┌─────────────────────────────────────────────────────────┐
│                  Admin Dashboard                         │
└─────────────────────────────────────────────────────────┘

┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│  Total Doc   │  │  Total Pat   │  │  Total Con   │
│    Active    │  │  Registered  │  │   (Month)    │
│     45       │  │     320      │  │      12      │
└──────────────┘  └──────────────┘  └──────────────┘

┌─────────────────────────────────────────────────────────┐
│              Konsultasi Per Bulan (6 Bulan)             │
│                    [Chart/Graph]                        │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                  Aktivitas Sistem Terbaru              │
│                                                         │
│ Waktu       │ User        │ Tipe              │ Deskripsi │
│ 10:30      │ Dr. Amin    │ Dokter Login      │ IP: ...  │
│ 10:15      │ Budi Santoso│ User Registrasi   │ Pasien   │
│ 09:45      │ Dr. Siti    │ Upload Konsultasi │ Hasil... │
│ ...        │ ...         │ ...               │ ...      │
└─────────────────────────────────────────────────────────┘
```

**UI Components**:
- 3 Stat Cards di atas
- Optional: Chart untuk statistik konsultasi per bulan (bisa pakai Chart.js atau Alpine.js)
- Tabel Activity Log dengan sorting/pagination
- Styling: Tailwind CSS (sudah ada di proyek)

---

## 7. ROUTES

### 7.1 Update routes/web.php
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
});
```

---

## 8. IMPLEMENTATION CHECKLIST

- [ ] 8.1 Buat Migration untuk `activity_logs` table
- [ ] 8.2 Buat Model `ActivityLog`
- [ ] 8.3 Buat Helper Class `ActivityLogger`
- [ ] 8.4 Buat Controller `AdminDashboardController`
- [ ] 8.5 Integrasikan Activity Logging di:
  - [ ] 8.5.1 User Registration
  - [ ] 8.5.2 Doctor Login
  - [ ] 8.5.3 Consultation Creation
  - [ ] 8.5.4 Doctor Approval/Rejection
  - [ ] 8.5.5 Diagnosis Upload
- [ ] 8.6 Buat Blade Template `admin/dashboard.blade.php`
- [ ] 8.7 Update Routes (routes/web.php)
- [ ] 8.8 Run Migration: `php artisan migrate`
- [ ] 8.9 Test Dashboard dengan data dummy
- [ ] 8.10 QA & Refinement

---

## 9. NOTES & CONSIDERATIONS

1. **Middleware**: Pastikan middleware `role:admin` sudah berfungsi dengan baik
2. **Performance**: Activity Log dapat tumbuh besar → pertimbangkan archiving/cleanup untuk log lama
3. **Chart**: Opsional gunakan Chart.js atau ApexCharts untuk visualisasi konsultasi per bulan
4. **Real-time**: Jika ingin real-time activity updates, pertimbangkan WebSocket (Laravel Broadcasting)
5. **Pagination**: Activity log table sebaiknya di-paginate (20-50 per halaman)
6. **Filters**: Tambahkan filter activity type dan date range untuk admin dashboard v2

---

## 10. ESTIMATED TIMELINE

- Migration & Models: 15 menit
- Controller & Helper: 20 menit
- Activity Logging Integration: 30 menit
- Blade Template & Styling: 20 menit
- Testing & QA: 15 menit

**Total: ~1.5 jam**
