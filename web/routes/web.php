<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\Api\EarscopeApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Earscope API Routes (tanpa auth, diakses dari Flask & dokter via AJAX)
|--------------------------------------------------------------------------
*/
Route::withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->prefix('api/earscope')
    ->group(function () {
        // Menerima hasil dari Flask (POST)
        Route::post('/diagnosis-result', [EarscopeApiController::class, 'receive'])
            ->name('api.earscope.receive');

        // Polling hasil terbaru oleh halaman diagnosa dokter (GET)
        Route::get('/latest-result', [EarscopeApiController::class, 'latest'])
            ->name('api.earscope.latest');

        // Menerima foto capture dari Flask (POST)
        Route::post('/upload-photo', [EarscopeApiController::class, 'uploadPhoto'])
            ->name('api.earscope.upload_photo');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['role:admin'])->prefix('admin')->group(function () { 
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // Doctor Management CRUD
        Route::prefix('doctors')->group(function () { 
            Route::get('/', [AdminController::class, 'indexDoctor'])->name('admin.doctors.index'); 
            Route::get('create', [AdminController::class, 'createDoctor'])->name('admin.doctors.create'); 
            Route::post('/', [AdminController::class, 'storeDoctor'])->name('admin.doctors.store'); 
            Route::get('{doctor}/edit', [AdminController::class, 'editDoctor'])->name('admin.doctors.edit'); 
            Route::patch('{doctor}', [AdminController::class, 'updateDoctor'])->name('admin.doctors.update'); 
            Route::delete('{doctor}', [AdminController::class, 'deleteDoctor'])->name('admin.doctors.delete'); 
        });

        // Patient List
        Route::get('patients', [AdminController::class, 'indexPatients'])->name('admin.patients.index');
    });

    Route::middleware(['role:doctor'])->group(function () {
        Route::get('/doctor/dashboard', [DoctorController::class, 'dashboard'])->name('doctor.dashboard');
        Route::get('/doctor/consultations', [DoctorController::class, 'consultations'])->name('doctor.consultations');
        Route::get('/doctor/consultation-results', [DoctorController::class, 'consultationResults'])->name('doctor.consultation.results');
        Route::get('/doctor/diagnoses', [DiagnosisController::class, 'index'])->name('doctor.diagnoses');
        Route::post('/doctor/diagnoses', [DiagnosisController::class, 'store'])->name('doctor.diagnoses.store');
        Route::delete('/doctor/consultation/{id}/retake', [DiagnosisController::class, 'retake'])->name('doctor.diagnoses.retake');
        Route::get('/doctor/patients-profile', [DoctorController::class, 'patientsProfile'])->name('doctor.patients-profile');
        Route::get('/doctor/consultation/{id}/details', [DoctorController::class, 'getConsultationDetails'])->name('consultation.details');
        Route::post('/doctor/consultation/{id}/approve', [DoctorController::class, 'approve'])->name('consultation.approve');
        Route::post('/doctor/consultation/{id}/reject', [DoctorController::class, 'reject'])->name('consultation.reject');
        Route::post('/doctor/consultation/{id}/schedule', [DoctorController::class, 'schedule'])->name('consultation.schedule');
        Route::post('/doctor/consultation/{id}/verify', [DoctorController::class, 'verifyDiagnosis'])->name('consultation.verify');
        Route::post('/doctor/consultation/{id}/save-notes', [DoctorController::class, 'saveNotes'])->name('consultation.save-notes');

        // Flask Earscope Control
        Route::post('/doctor/flask/start', [DoctorController::class, 'startFlask'])->name('doctor.flask.start');
        Route::get('/doctor/flask/status', [DoctorController::class, 'flaskStatus'])->name('doctor.flask.status');
    });

    Route::middleware(['role:patient'])->group(function () {
        Route::get('/patient/dashboard', [PatientController::class, 'dashboard'])->name('patient.dashboard');
        Route::get('/patient/create-consultation', [PatientController::class, 'createConsultation'])->name('patient.create-consultation');
        Route::post('/patient/store-consultation', [PatientController::class, 'storeConsultation'])->name('patient.store-consultation');
        Route::get('/patient/consultation-results', [PatientController::class, 'consultationResults'])->name('patient.consultation.results');
        Route::get('/patient/consultation/{id}/details', [PatientController::class, 'getConsultationDetails'])->name('patient.consultation.details');
        Route::post('/patient/consultation/{id}/cancel', [PatientController::class, 'cancelConsultation'])->name('patient.consultation.cancel');
    });
});


require __DIR__.'/auth.php';
