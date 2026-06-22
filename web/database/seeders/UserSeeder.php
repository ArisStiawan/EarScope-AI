<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\ConsultationRequest;
use App\Models\Diagnosis;
use App\Models\DiagnosisImage;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // =====================
        // ADMIN
        // =====================
        $admin = User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin'
        ]);

        // =====================
        // DOCTOR (Aktor Dokter 1)
        // =====================
        $doctorUser = User::create([
            'username' => 'doctor1',
            'password' => Hash::make('doctor123'),
            'role' => 'doctor'
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'name' => 'Dr. John Doe',
            'license_number' => 'STR-01',
            'gender' => 'male'
        ]);

        // =====================
        // PATIENT (Aktor Pasien 1)
        // =====================
        $patientUser = User::create([
            'username' => 'patient1',
            'email' => 'jane.doe@example.com',
            'password' => Hash::make('patient123'),
            'role' => 'patient'
        ]);

        $patient = Patient::create([
            'user_id' => $patientUser->id,
            'name' => 'Jane Doe',
            'birth_date' => '1995-05-15',
            'age' => 31,
            'address' => 'Jl. Kebon Jeruk No. 123',
            'gender' => 'female'
        ]);

        // =====================
        // MOCK CONSULTATIONS (20)
        // =====================
        $complaints = [
            'Telinga kanan terasa sangat gatal dan nyeri ringan selama 3 hari terakhir.',
            'Telinga kiri terasa tersumbat/penuh air setelah berenang kemarin sore.',
            'Nyeri berdenyut di dalam telinga kanan disertai sakit tenggorokan dan demam.',
            'Pendengaran telinga kiri mendadak berkurang secara signifikan pagi ini.',
            'Telinga mendengung (tinnitus) cukup keras dan konstan sejak kemarin malam.',
            'Keluar cairan berwarna kekuningan dan berbau tidak sedap dari telinga kanan.',
            'Telinga gatal hebat dan terasa ada sesuatu yang menyumbat di liang telinga.',
            'Nyeri tajam pada telinga kiri saat mengunyah makanan atau membuka mulut.',
            'Kulit di sekitar liang telinga luar terlihat memerah dan terasa perih.',
            'Telinga tersumbat setelah mengalami batuk pilek selama seminggu ini.'
        ];

        $diagnosesList = [
            'Otitis Media Akut',
            'Otitis Eksterna',
            'Serumen Obsturans (Kotoran Telinga Menyumbat)',
            'Miringitis Bulosa',
            'Tinitus Subjektif',
            'Otitis Media Supuratif Kronis (OMSK)',
            'Normal'
        ];

        // 1. Pending consultations (5 requests)
        for ($i = 1; $i <= 5; $i++) {
            ConsultationRequest::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'complaint' => $complaints[($i - 1) % count($complaints)] . " (Pending #$i)",
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays($i)->subHours($i * 2),
            ]);
        }

        // 2. Scheduled consultations (5 requests)
        for ($i = 1; $i <= 5; $i++) {
            $scheduledDate = Carbon::now()->addDays($i);
            ConsultationRequest::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'complaint' => $complaints[($i + 4) % count($complaints)] . " (Jadwal #$i)",
                'status' => 'scheduled',
                'scheduled_date' => $scheduledDate->toDateString(),
                'scheduled_time' => sprintf('%02d:00:00', 9 + $i),
                'created_at' => Carbon::now()->subDays($i + 5),
            ]);
        }

        // 3. Cancelled consultations (3 requests)
        for ($i = 1; $i <= 3; $i++) {
            ConsultationRequest::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'complaint' => $complaints[($i + 2) % count($complaints)] . " (Batal #$i)",
                'status' => 'cancelled',
                'notes' => 'Pembatalan otomatis oleh sistem karena melewati jadwal konsultasi.',
                'created_at' => Carbon::now()->subDays($i + 10),
            ]);
        }

        // 4. Done consultations with Diagnoses (7 requests)
        for ($i = 1; $i <= 7; $i++) {
            $c = ConsultationRequest::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'complaint' => $complaints[($i) % count($complaints)] . " (Selesai #$i)",
                'status' => 'done',
                'notes' => 'Pasien kooperatif selama pemeriksaan otoskopi berlangsung.',
                'scheduled_date' => Carbon::now()->subDays($i + 2)->toDateString(),
                'scheduled_time' => '10:00:00',
                'created_at' => Carbon::now()->subDays($i + 15),
            ]);

            $diagResult = $diagnosesList[($i - 1) % count($diagnosesList)];

            $diagnosis = Diagnosis::create([
                'consultation_request_id' => $c->id,
                'diagnosis_result' => $diagResult,
                'ai_result' => $diagResult === 'Normal' ? 'Normal' : 'Abnormal',
                'notes' => "Terapi/Saran: Jaga telinga tetap kering, bersihkan secara berkala ke klinik, hindari penggunaan cotton bud berlebih.",
                'is_verified' => true,
            ]);

            DiagnosisImage::create([
                'diagnosis_id' => $diagnosis->id,
                'image_path' => 'diagnoses/mock_ear_' . $i . '.jpg',
                'ai_screening_result' => [
                    'class' => $diagResult,
                    'confidence' => round(0.75 + ($i * 0.03), 2),
                    'timestamp' => Carbon::now()->subDays($i + 2)->toIso8601String()
                ]
            ]);
        }
    }
}