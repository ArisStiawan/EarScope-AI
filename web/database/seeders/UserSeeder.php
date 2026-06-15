<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Hash;

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
        // DOCTOR
        // =====================
        $doctorUser = User::create([
            'username' => 'doctor1',
            'password' => Hash::make('doctor123'),
            'role' => 'doctor'
        ]);

        Doctor::create([
            'user_id' => $doctorUser->id,
            'name' => 'Dr. John Doe',
            'license_number' => 'STR-01',
            'gender' => 'male',
            'specialization' => 'Umum'
        ]);

        // =====================
        // PATIENT
        // =====================
        $patientUser = User::create([
            'username' => 'patient1',
            'password' => Hash::make('patient123'),
            'role' => 'patient'
        ]);

        Patient::create([
            'user_id' => $patientUser->id,
            'name' => 'Jane Doe',
            'birth_date' => '1995-05-15',
            'age' => 31,
            'address' => 'Jl. Kebon Jeruk No. 123',
            'email' => 'jane.doe@example.com',
            'gender' => 'female'
        ]);
    }
}