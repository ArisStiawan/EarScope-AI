<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ConsultationRequest;
use App\Models\Patient;
use App\Models\Doctor;
use Carbon\Carbon;

class ConsultationRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patient = Patient::first();
        $doctor = Doctor::first();

        // Pastikan ada pasien dan dokter sebelum membuat seeder
        if ($patient && $doctor) {
            ConsultationRequest::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'complaint' => 'Sering merasa pusing dan telinga berdenging setelah berenang.',
                'scheduled_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'scheduled_time' => '10:00:00',
                'status' => 'pending'
            ]);

            ConsultationRequest::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'complaint' => 'Telinga kanan terasa sakit saat ditekan bagian bawah.',
                'scheduled_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'scheduled_time' => '14:30:00',
                'status' => 'done'
            ]);
            
            ConsultationRequest::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'complaint' => 'Pendengaran dirasa berkurang sejak 3 hari yang lalu secara tiba-tiba.',
                'scheduled_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'scheduled_time' => '09:00:00',
                'status' => 'scheduled'
            ]);
        }
    }
}
