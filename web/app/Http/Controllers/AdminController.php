<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\ConsultationRequest;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Builder;

class AdminController extends Controller
{
    /**
     * Display admin dashboard with statistics and activity logs
     */
    public function dashboard(): View
    {
        // Get statistics
        $stats = [
            'total_doctors' => $this->getTotalActiveDoctors(),
            'total_patients' => $this->getTotalPatients(),
            'total_consultations_month' => $this->getTotalConsultationsThisMonth(),
        ];

        // Get monthly consultation statistics (6 months)
        $consultationStats = $this->getMonthlyConsultationStats();

        // Get activity logs
        $activityLogs = $this->getActivityLogs(50);

        return view('admin.dashboard', [
            'stats' => $stats,
            'consultationStats' => $consultationStats,
            'activityLogs' => $activityLogs,
            'formatActivityType' => fn($type) => $this->formatActivityType($type),
            'getActivityBadgeClass' => fn($type) => $this->getActivityBadgeClass($type),
        ]);
    }

    /**
     * Get total active doctors
     */
    private function getTotalActiveDoctors(): int
    {
        return Doctor::count();
    }

    /**
     * Get total registered patients
     */
    private function getTotalPatients(): int
    {
        return Patient::count();
    }

    /**
     * Get total consultations this month
     */
    private function getTotalConsultationsThisMonth(): int
    {
        return ConsultationRequest::whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->count();
    }

    /**
     * Get monthly consultation statistics (last 6 months)
     */
    private function getMonthlyConsultationStats(): array
    {
        $months = [];
        $data = [];

        // Get data for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->format('M Y');

            $months[] = $monthLabel;

            $count = ConsultationRequest::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $data[] = $count;
        }

        return [
            'labels' => $months,
            'data' => $data,
        ];
    }

    /**
     * Get activity logs with user information
     */
    private function getActivityLogs($limit = 50): array
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->limit($limit)
            ->get();

        return $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'timestamp' => $log->created_at,
                'user_name' => $log->user?->username ?? 'System',
                'activity_type' => $log->activity_type,
                'description' => $log->description,
                'ip_address' => $log->ip_address,
                'data' => $log->data,
            ];
        })->toArray();
    }

    /**
     * Format activity type to human readable
     */
    public function formatActivityType($type): string
    {
        $types = [
            'user_registered' => 'User Registrasi',
            'doctor_login' => 'Dokter Login',
            'consultation_requested' => 'Konsultasi Diminta',
            'consultation_approved' => 'Konsultasi Disetujui',
            'consultation_rejected' => 'Konsultasi Ditolak',
            'consultation_uploaded' => 'Hasil Upload',
        ];

        return $types[$type] ?? ucfirst($type);
    }

    /**
     * Get badge class for activity type
     */
    public function getActivityBadgeClass($type): string
    {
        $classes = [
            'user_registered' => 'bg-green-100 text-green-800',
            'doctor_login' => 'bg-blue-100 text-blue-800',
            'consultation_requested' => 'bg-yellow-100 text-yellow-800',
            'consultation_approved' => 'bg-green-100 text-green-800',
            'consultation_rejected' => 'bg-red-100 text-red-800',
            'consultation_uploaded' => 'bg-purple-100 text-purple-800',
        ];

        return $classes[$type] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Show create doctor form
     */
    public function createDoctor()
    {
        return view('admin.doctors.create');
    }

    /**
     * Store new doctor
     */
    public function storeDoctor(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'password' => 'required',
            'name' => 'required',
            'license_number' => 'required',
            'specialization' => 'required',
            'gender' => 'required|in:male,female',
        ]);

        // buat user
        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'doctor',
        ]);

        // buat doctor
        Doctor::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'license_number' => $request->license_number,
            'specialization' => $request->specialization,
            'gender' => $request->gender,
        ]);

        return redirect()->route('admin.doctors.index')->with('success', 'Doctor berhasil ditambahkan');
    }

    public function editDoctor($doctor)
    {
        $doctor = Doctor::with('user')->findOrFail($doctor);

        return view('admin.doctors.edit', compact('doctor'));
    }

    public function updateDoctor(Request $request, $doctor)
    {
        $doctor = Doctor::findOrFail($doctor);

        $request->validate([
            'name' => 'required',
            'license_number' => 'required',
            'specialization' => 'required',
            'gender' => 'required|in:male,female',
        ]);

        $doctor->update([
            'name' => $request->name,
            'license_number' => $request->license_number,
            'specialization' => $request->specialization,
            'gender' => $request->gender,
        ]);

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Dokter berhasil diupdate');
    }

    public function deleteDoctor($doctor)
    {
        $doctor = Doctor::findOrFail($doctor);

        // hapus user juga
        $doctor->user()->delete();
        $doctor->delete();

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Dokter berhasil dihapus');
    }

    public function indexDoctor()
    {
        $doctors = Doctor::with('user')->latest()->get();

        return view('admin.doctors.index', compact('doctors'));
    }

    /**
     * Show all registered patients (username & registration date only)
     */
    public function indexPatients()
    {
        $patients = User::where('role', 'patient')
            ->select('id', 'username', 'created_at')
            ->latest()
            ->get();

        return view('admin.patients.index', compact('patients'));
    }
}