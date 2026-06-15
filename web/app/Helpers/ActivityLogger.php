<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ActivityLogger
{
    /**
     * Log an activity
     *
     * @param string $type - Activity type (enum value)
     * @param string $description - Human readable description
     * @param array|null $data - Optional JSON data
     * @param int|null $userId - User ID (default: current auth user)
     * @return ActivityLog
     */
    public static function log($type, $description, $data = null, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        $request = app(Request::class);
        $ipAddress = $request->ip() ?? '';
        $userAgent = $request->userAgent() ?? '';

        return ActivityLog::create([
            'user_id' => $userId,
            'activity_type' => $type,
            'description' => $description,
            'data' => $data,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Log user registration
     */
    public static function logUserRegistered($user, $role = null)
    {
        return self::log(
            'user_registered',
            "User '{$user->username}' baru registrasi sebagai " . ($role ?? $user->role),
            ['user_id' => $user->id, 'role' => $role ?? $user->role],
            $user->id
        );
    }

    /**
     * Log doctor login
     */
    public static function logDoctorLogin($user)
    {
        return self::log(
            'doctor_login',
            "Dokter '{$user->username}' login ke sistem",
            ['user_id' => $user->id],
            $user->id
        );
    }

    /**
     * Log consultation request
     */
    public static function logConsultationRequested($consultation, $patient)
    {
        return self::log(
            'consultation_requested',
            "Pasien '{$patient->name}' membuat permintaan konsultasi baru",
            [
                'consultation_id' => $consultation->id,
                'patient_id' => $patient->id,
                'complaint' => $consultation->complaint,
            ],
            $patient->user_id
        );
    }

    /**
     * Log consultation approved
     */
    public static function logConsultationApproved($consultation, $doctor)
    {
        return self::log(
            'consultation_approved',
            "Dokter '{$doctor->name}' menyetujui permintaan konsultasi dari pasien",
            [
                'consultation_id' => $consultation->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $consultation->patient_id,
            ],
            $doctor->user_id
        );
    }

    /**
     * Log consultation rejected
     */
    public static function logConsultationRejected($consultation, $doctor)
    {
        return self::log(
            'consultation_rejected',
            "Dokter '{$doctor->name}' menolak permintaan konsultasi dari pasien",
            [
                'consultation_id' => $consultation->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $consultation->patient_id,
            ],
            $doctor->user_id
        );
    }

    /**
     * Log consultation uploaded (diagnosis result)
     */
    public static function logConsultationUploaded($diagnosis, $doctor)
    {
        return self::log(
            'consultation_uploaded',
            "Dokter '{$doctor->name}' mengunggah hasil konsultasi",
            [
                'diagnosis_id' => $diagnosis->id,
                'consultation_id' => $diagnosis->consultation_id,
                'doctor_id' => $doctor->id,
            ],
            $doctor->user_id
        );
    }
}
