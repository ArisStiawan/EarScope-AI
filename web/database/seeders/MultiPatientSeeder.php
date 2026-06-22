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

class MultiPatientSeeder extends Seeder
{
    public function run(): void
    {
        // Pakai dokter yang sudah ada (doctor1)
        $doctorUser = User::where('username', 'doctor1')->first();
        if (!$doctorUser) {
            $this->command->error('Doctor (doctor1) tidak ditemukan. Jalankan UserSeeder terlebih dahulu.');
            return;
        }
        $doctor = $doctorUser->doctor;

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
            'Telinga tersumbat setelah mengalami batuk pilek selama seminggu ini.',
        ];

        $diagnosesList = [
            'Otitis Media Akut',
            'Otitis Eksterna',
            'Serumen Obsturans (Kotoran Telinga Menyumbat)',
            'Miringitis Bulosa',
            'Tinitus Subjektif',
            'Otitis Media Supuratif Kronis (OMSK)',
            'Normal',
        ];

        // Data dummy 20 pasien
        $patientData = [
            ['name' => 'Ahmad Fauzi',       'gender' => 'male',   'birth' => '1990-03-12', 'address' => 'Jl. Merdeka No. 1',       'email' => 'ahmad.fauzi@example.com'],
            ['name' => 'Siti Rahayu',       'gender' => 'female', 'birth' => '1993-07-22', 'address' => 'Jl. Pahlawan No. 5',      'email' => 'siti.rahayu@example.com'],
            ['name' => 'Budi Santoso',      'gender' => 'male',   'birth' => '1988-11-05', 'address' => 'Jl. Sudirman No. 10',     'email' => 'budi.santoso@example.com'],
            ['name' => 'Rina Kusuma',       'gender' => 'female', 'birth' => '1997-01-30', 'address' => 'Jl. Diponegoro No. 3',    'email' => 'rina.kusuma@example.com'],
            ['name' => 'Eko Prasetyo',      'gender' => 'male',   'birth' => '1985-06-18', 'address' => 'Jl. Gatot Subroto No. 7', 'email' => 'eko.prasetyo@example.com'],
            ['name' => 'Dewi Lestari',      'gender' => 'female', 'birth' => '1999-04-25', 'address' => 'Jl. Ahmad Yani No. 21',   'email' => 'dewi.lestari@example.com'],
            ['name' => 'Hendra Wijaya',     'gender' => 'male',   'birth' => '1991-09-14', 'address' => 'Jl. Soekarno Hatta No. 2','email' => 'hendra.wijaya@example.com'],
            ['name' => 'Fitri Handayani',   'gender' => 'female', 'birth' => '1995-12-02', 'address' => 'Jl. Imam Bonjol No. 8',   'email' => 'fitri.handayani@example.com'],
            ['name' => 'Rudi Hartono',      'gender' => 'male',   'birth' => '1987-08-09', 'address' => 'Jl. Veteran No. 14',      'email' => 'rudi.hartono@example.com'],
            ['name' => 'Anisa Putri',       'gender' => 'female', 'birth' => '2000-02-17', 'address' => 'Jl. Pemuda No. 6',        'email' => 'anisa.putri@example.com'],
            ['name' => 'Rizky Maulana',     'gender' => 'male',   'birth' => '1994-05-28', 'address' => 'Jl. Kartini No. 11',      'email' => 'rizky.maulana@example.com'],
            ['name' => 'Yuni Astuti',       'gender' => 'female', 'birth' => '1992-10-03', 'address' => 'Jl. Cut Nyak Dien No. 9', 'email' => 'yuni.astuti@example.com'],
            ['name' => 'Dimas Arya',        'gender' => 'male',   'birth' => '1996-07-11', 'address' => 'Jl. Hayam Wuruk No. 4',   'email' => 'dimas.arya@example.com'],
            ['name' => 'Nurul Aini',        'gender' => 'female', 'birth' => '1989-03-27', 'address' => 'Jl. Teuku Umar No. 16',   'email' => 'nurul.aini@example.com'],
            ['name' => 'Fajar Setiawan',    'gender' => 'male',   'birth' => '1998-11-19', 'address' => 'Jl. Trunojoyo No. 13',    'email' => 'fajar.setiawan@example.com'],
            ['name' => 'Maya Indah',        'gender' => 'female', 'birth' => '1993-06-06', 'address' => 'Jl. Lombok No. 18',       'email' => 'maya.indah@example.com'],
            ['name' => 'Agus Triono',       'gender' => 'male',   'birth' => '1986-01-23', 'address' => 'Jl. Bali No. 20',         'email' => 'agus.triono@example.com'],
            ['name' => 'Lia Permata',       'gender' => 'female', 'birth' => '2001-09-08', 'address' => 'Jl. Flores No. 15',       'email' => 'lia.permata@example.com'],
            ['name' => 'Bagas Nugroho',     'gender' => 'male',   'birth' => '1990-04-16', 'address' => 'Jl. Sulawesi No. 12',     'email' => 'bagas.nugroho@example.com'],
            ['name' => 'Salma Azzahra',     'gender' => 'female', 'birth' => '1997-08-31', 'address' => 'Jl. Kalimantan No. 17',   'email' => 'salma.azzahra@example.com'],
        ];

        // Status konsultasi yang akan di-rotate
        $statuses = ['pending', 'scheduled', 'done', 'cancelled'];

        foreach ($patientData as $idx => $data) {
            $username = 'patient_' . str_pad($idx + 2, 2, '0', STR_PAD_LEFT);

            // Buat user
            $user = User::create([
                'username' => $username,
                'email'    => $data['email'],
                'password' => Hash::make('patient123'),
                'role'     => 'patient',
            ]);

            // Buat profil pasien
            $patient = Patient::create([
                'user_id'    => $user->id,
                'name'       => $data['name'],
                'birth_date' => $data['birth'],
                'age'        => Carbon::parse($data['birth'])->age,
                'address'    => $data['address'],
                'gender'     => $data['gender'],
            ]);

            // Tentukan status konsultasi secara bergilir
            $status = $statuses[$idx % count($statuses)];

            $consultationData = [
                'patient_id' => $patient->id,
                'doctor_id'  => $doctor->id,
                'complaint'  => $complaints[$idx % count($complaints)],
                'status'     => $status,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
            ];

            if ($status === 'scheduled') {
                $consultationData['scheduled_date'] = Carbon::now()->addDays(rand(1, 14))->toDateString();
                $consultationData['scheduled_time'] = sprintf('%02d:00:00', rand(8, 16));
            }

            if ($status === 'done') {
                $consultationData['scheduled_date'] = Carbon::now()->subDays(rand(1, 15))->toDateString();
                $consultationData['scheduled_time'] = '10:00:00';
                $consultationData['notes'] = 'Pasien kooperatif selama pemeriksaan otoskopi berlangsung.';
            }

            if ($status === 'cancelled') {
                $consultationData['notes'] = 'Pembatalan oleh pasien.';
            }

            $consultation = ConsultationRequest::create($consultationData);

            // Buat diagnosis untuk konsultasi yang sudah selesai
            if ($status === 'done') {
                $diagResult = $diagnosesList[$idx % count($diagnosesList)];

                $diagnosis = Diagnosis::create([
                    'consultation_request_id' => $consultation->id,
                    'diagnosis_result'        => $diagResult,
                    'ai_result'               => $diagResult === 'Normal' ? 'Normal' : 'Abnormal',
                    'notes'                   => 'Terapi/Saran: Jaga telinga tetap kering, bersihkan secara berkala ke klinik, hindari penggunaan cotton bud berlebih.',
                    'is_verified'             => true,
                ]);

                DiagnosisImage::create([
                    'diagnosis_id'        => $diagnosis->id,
                    'image_path'          => 'diagnoses/mock_ear_' . ($idx + 1) . '.jpg',
                    'ai_screening_result' => [
                        'class'      => $diagResult,
                        'confidence' => round(0.75 + ($idx * 0.01), 2),
                        'timestamp'  => Carbon::now()->subDays($idx + 1)->toIso8601String(),
                    ],
                ]);
            }
        }

        $this->command->info('✅ MultiPatientSeeder: 20 pasien berhasil dibuat, semua konsul ke ' . $doctor->name . '.');
    }
}
