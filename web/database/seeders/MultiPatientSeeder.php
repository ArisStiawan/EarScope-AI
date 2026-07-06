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

        // Bersihkan data lama dari seeder ini (patient_02 s/d patient_16)
        $oldUsernames = array_map(fn($i) => 'patient_' . str_pad($i, 2, '0', STR_PAD_LEFT), range(2, 16));
        $oldUsers = User::whereIn('username', $oldUsernames)->get();
        foreach ($oldUsers as $oldUser) {
            if ($oldUser->patient) {
                ConsultationRequest::where('patient_id', $oldUser->patient->id)->delete();
                $oldUser->patient->delete();
            }
            $oldUser->delete();
        }

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
        ];

        // -------------------------------------------------------
        // BAGIAN 1: Isi kuota hari ini (6 Juli 2026) — 15 pasien
        // -------------------------------------------------------
        $today = '2026-07-06';
        $times = ['08:00:00', '08:30:00', '09:00:00', '09:30:00', '10:00:00',
                  '10:30:00', '11:00:00', '11:30:00', '12:00:00', '13:00:00',
                  '13:30:00', '14:00:00', '14:30:00', '15:00:00', '15:30:00'];

        foreach ($patientData as $idx => $data) {
            $queueNo  = $idx + 1; // 1 – 15
            $username = 'patient_' . str_pad($idx + 2, 2, '0', STR_PAD_LEFT);

            $user = User::create([
                'username' => $username,
                'email'    => $data['email'],
                'password' => Hash::make('patient123'),
                'role'     => 'patient',
            ]);

            $patient = Patient::create([
                'user_id'    => $user->id,
                'name'       => $data['name'],
                'birth_date' => $data['birth'],
                'age'        => Carbon::parse($data['birth'])->age,
                'address'    => $data['address'],
                'gender'     => $data['gender'],
            ]);

            $consultationData = [
                'patient_id'     => $patient->id,
                'doctor_id'      => $doctor->id,
                'complaint'      => $complaints[$idx % count($complaints)],
                'status'         => 'scheduled',
                'scheduled_date' => $today,
                'scheduled_time' => $times[$idx],
                'queue_number'   => $queueNo,
                'created_at'     => Carbon::parse($today)->subDays(rand(1, 5)),
            ];

            ConsultationRequest::create($consultationData);
        }

        $this->command->info('✅ MultiPatientSeeder: 15 pasien berhasil dibuat dengan antrean 1–15 di tanggal ' . $today . ' untuk dr. ' . $doctor->name . '.');
    }
}
