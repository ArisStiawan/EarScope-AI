<?php

namespace App\Http\Controllers;

use App\Models\ConsultationRequest;
use App\Models\Diagnosis;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient;

class DoctorController extends Controller
{
    private function getDoctor()
    {
        $user = Auth::user();

        if (!$user || !$user->doctor) {
            abort(403, 'Doctor not found');
        }

        return $user->doctor;
    }

    private function authorizeDoctor($consultation)
    {
        $doctor = $this->getDoctor();

        if ($consultation->doctor_id !== $doctor->id) {
            abort(403, 'Unauthorized');
        }
    }


    public function dashboard(Request $request)
    {
        $doctor = $this->getDoctor();

        // Jumlah konsultasi yang masih menunggu persetujuan
        $pendingCount = ConsultationRequest::where('doctor_id', $doctor->id)
            ->where('status', 'pending')
            ->count();

        // Jumlah konsultasi yang dijadwalkan hari ini (scheduled today)
        $todayScheduleCount = ConsultationRequest::where('doctor_id', $doctor->id)
            ->where('status', 'scheduled')
            ->whereDate('scheduled_date', Carbon::today())
            ->count();

        // Jumlah pasien unik yang pernah ditangani (semua status selain pending)
        $patientsHandledCount = ConsultationRequest::where('doctor_id', $doctor->id)
            ->whereIn('status', ['scheduled', 'done', 'cancelled'])
            ->distinct('patient_id')
            ->count('patient_id');

        $filter = $request->get('filter', 'all');

        $query = ConsultationRequest::where('doctor_id', $doctor->id)
            ->with(['patient.user'])
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '>=', Carbon::today());

        if ($filter === 'today') {
            $query->whereDate('scheduled_date', Carbon::today());
        } elseif ($filter === 'week') {
            $query->whereBetween('scheduled_date', [Carbon::today()->startOfWeek(), Carbon::today()->endOfWeek()]);
        } elseif ($filter === 'month') {
            $query->whereMonth('scheduled_date', Carbon::today()->month)
                  ->whereYear('scheduled_date', Carbon::today()->year);
        }

        $perPage = $request->input('per_page', 5);
        if (!in_array($perPage, [5, 10, 15])) {
            $perPage = 5;
        }

        $consultations = $query->orderBy('scheduled_date', 'asc')
            ->orderBy('queue_number', 'asc')
            ->paginate($perPage);

        $pendingRequests = ConsultationRequest::where('doctor_id', $doctor->id)
            ->where('status', 'pending')
            ->with(['patient.user'])
            ->latest()
            ->get();

        return view('doctor.dashboard', compact(
            'pendingCount',
            'todayScheduleCount',
            'patientsHandledCount',
            'consultations',
            'filter',
            'pendingRequests',
            'perPage'
        ));
    }

    public function consultationResults(Request $request)
    {
        $doctor = $this->getDoctor();

        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 15])) {
            $perPage = 10;
        }

        $consultations = ConsultationRequest::where('doctor_id', $doctor->id)
            ->with(['patient.user', 'diagnosis'])
            ->where('status', 'done')
            ->orderBy('scheduled_date', 'desc')
            ->paginate($perPage);

        return view('doctor.consultation-result', compact('consultations', 'perPage'));
    }

    public function approve($id)
    {
        $consultation = ConsultationRequest::findOrFail($id);
        $this->authorizeDoctor($consultation);

        $consultation->update(['status' => 'scheduled']);
        
        // Log consultation approval
        $doctor = $this->getDoctor();
        ActivityLogger::logConsultationApproved($consultation, $doctor);

        return response()->json([
            'message' => 'Consultation scheduled successfully',
            'status' => $consultation->status
        ]);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255'
        ]);

        $consultation = ConsultationRequest::findOrFail($id);
        $this->authorizeDoctor($consultation);

        if ($consultation->status === 'scheduled' && $consultation->scheduled_date) {
            ConsultationRequest::where('doctor_id', $consultation->doctor_id)
                ->whereIn('status', ['scheduled', 'done'])
                ->whereDate('scheduled_date', $consultation->scheduled_date)
                ->where('queue_number', '>', $consultation->queue_number)
                ->decrement('queue_number');
        }

        $consultation->update([
            'status' => 'cancelled',
            'queue_number' => null
        ]);
        
        // Log consultation rejection
        $doctor = $this->getDoctor();
        ActivityLogger::logConsultationRejected($consultation, $doctor);

        return response()->json([
            'message' => 'Consultation rejected successfully',
            'status' => $consultation->status
        ]);
    }

    public function schedule(Request $request, $id)
    {
        $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today'
        ]);

        $consultation = ConsultationRequest::findOrFail($id);
        $this->authorizeDoctor($consultation);

        if (!in_array($consultation->status, ['pending', 'scheduled'])) {
            return response()->json([
                'error' => 'Consultation cannot be scheduled in its current state'
            ], 400);
        }

        // Prevent rescheduling if diagnosis already exists
        if ($consultation->status === 'scheduled' && $consultation->diagnosis) {
            return response()->json([
                'error' => 'Konsultasi sudah memiliki diagnosis, tidak dapat dijadwalkan ulang'
            ], 400);
        }

        $wasAlreadyScheduled = $consultation->status === 'scheduled';
        $doctor = $this->getDoctor();

        $scheduledCount = ConsultationRequest::where('doctor_id', $doctor->id)
            ->whereIn('status', ['scheduled', 'done'])
            ->whereDate('scheduled_date', $request->scheduled_date)
            ->when($wasAlreadyScheduled, function($query) use ($consultation) {
                // Don't count the current consultation if it's already scheduled for this date
                return $query->where('id', '!=', $consultation->id);
            })
            ->count();

        // Check patient quota for the selected date
        if ($doctor->patient_quota > 0) {
            if ($scheduledCount >= $doctor->patient_quota) {
                return response()->json([
                    'error' => 'Kuota pasien untuk tanggal tersebut sudah penuh (' . $doctor->patient_quota . ' pasien).'
                ], 400);
            }
        }

        $queueNumber = $consultation->queue_number;
        // If not already scheduled on this date, get a new queue number
        if (!$wasAlreadyScheduled || $consultation->scheduled_date !== $request->scheduled_date) {
            $queueNumber = $scheduledCount + 1;

            // Re-order the queue on the old date if it was previously scheduled
            if ($wasAlreadyScheduled && $consultation->scheduled_date) {
                ConsultationRequest::where('doctor_id', $doctor->id)
                    ->whereIn('status', ['scheduled', 'done'])
                    ->whereDate('scheduled_date', $consultation->scheduled_date)
                    ->where('queue_number', '>', $consultation->queue_number)
                    ->decrement('queue_number');
            }
        }

        $consultation->update([
            'status'         => 'scheduled',
            'scheduled_date' => $request->scheduled_date,
            'queue_number'   => $queueNumber,
        ]);

        // Log approval only if it was still pending
        if (!$wasAlreadyScheduled) {
            $doctor = $this->getDoctor();
            ActivityLogger::logConsultationApproved($consultation, $doctor);
        }

        // Send notification email to the patient
        if ($consultation->patient && $consultation->patient->user && $consultation->patient->user->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($consultation->patient->user->email)
                    ->send(new \App\Mail\ConsultationScheduledMail($consultation));
            } catch (\Exception $e) {
                // Log the error but don't stop the schedule process
                \Illuminate\Support\Facades\Log::error('Failed to send consultation schedule email: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message'        => 'Consultation scheduled successfully',
            'scheduled_date' => $consultation->scheduled_date,
            'queue_number'   => $consultation->queue_number,
        ]);
    }

    public function getConsultationDetails($id)
    {
        $consultation = ConsultationRequest::with('patient.user')->findOrFail($id);
        $this->authorizeDoctor($consultation);

        $diagnosisData = null;
        if ($consultation->diagnosis) {
            $d = $consultation->diagnosis;
            $diagnosisData = [
                'id' => $d->id,
                'diagnosis_result' => $d->diagnosis_result,
                'notes' => $d->notes,
                'is_verified' => $d->is_verified,
                'images' => $d->images->map(fn($img) => [
                    'id' => $img->id,
                    'image_url' => asset('storage/' . $img->image_path),
                    'ai_screening_result' => $img->ai_screening_result,
                    'created_at' => $img->created_at,
                ]),
            ];
        }

        return response()->json([
            'id'             => $consultation->id,
            'complaint'      => $consultation->complaint,
            'notes'          => $consultation->notes,
            'status'         => $consultation->status,
            'created_at'     => $consultation->created_at,
            'scheduled_date' => $consultation->scheduled_date,
            'queue_number'   => $consultation->queue_number,
            'diagnosis'      => $diagnosisData,
            'patient'        => $consultation->patient ? [
                'id'         => $consultation->patient->id,
                'name'       => $consultation->patient->name,
                'age'        => $consultation->patient->age,
                'medical_record_number' => $consultation->patient->medical_record_number,
                'email'      => $consultation->patient->user->email ?? null,
                'gender'     => $consultation->patient->gender,
                'address'    => $consultation->patient->address,
                'birth_date' => $consultation->patient->birth_date,
            ] : null,
        ]);
    }

    public function showConsultation($id)
    {
        $consultation = ConsultationRequest::findOrFail($id);
        $this->authorizeDoctor($consultation);

        return view('doctor.consultation-detail', compact('consultation'));
    }

    public function consultations(Request $request)
    {
        $doctor = $this->getDoctor();
        $status = $request->get('status', 'all');

        $query = ConsultationRequest::where('doctor_id', $doctor->id)
            ->with(['patient.user', 'diagnosis.images']);

        if ($status !== 'all') {
            $query->where('status', $status)
                  ->latest();
        } else {
            $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'scheduled' THEN 1 WHEN status = 'cancelled' THEN 2 WHEN status = 'done' THEN 3 ELSE 4 END")
                  ->orderBy('created_at', 'desc');
        }

        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 15])) {
            $perPage = 10;
        }

        $consultations = $query->paginate($perPage);

        return view('doctor.consultations', compact('consultations', 'status', 'perPage'));
    }

    public function verifyDiagnosis(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $consultation = ConsultationRequest::findOrFail($id);
        $this->authorizeDoctor($consultation);

        $diagnosis = $consultation->diagnosis;

        if (!$diagnosis) {
            // Jika hasil Jetson belum diunggah tetapi dokter ingin menyelesaikan secara manual
            $diagnosis = Diagnosis::create([
                'consultation_request_id' => $consultation->id,
                'diagnosis_result'        => 'Diagnosis Manual',
                'is_verified'             => true,
                'notes'                   => $request->notes,
            ]);
        } else {
            $diagnosis->update([
                'notes'       => $request->notes,
                'is_verified' => true,
            ]);
        }

        // Simpan catatan dokter ke consultation_requests.notes
        $consultation->update([
            'status' => 'done',
            'notes'  => $request->notes,
        ]);

        // Log activity
        $doctor = $this->getDoctor();
        ActivityLogger::log(
            'consultation_verified',
            "Dokter '{$doctor->name}' memverifikasi diagnosis pasien '{$consultation->patient->name}'",
            [
                'consultation_id' => $consultation->id,
                'diagnosis_id'    => $diagnosis->id,
                'doctor_id'       => $doctor->id,
                'notes'           => $request->notes,
            ],
            $doctor->user_id
        );

        return redirect()->route('doctor.consultations')->with('success', 'Konsultasi berhasil diverifikasi dan diselesaikan.');
    }

    /**
     * Simpan catatan dokter ke consultation_requests.notes (tanpa verifikasi/selesaikan).
     */
    public function saveNotes(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $consultation = ConsultationRequest::findOrFail($id);
        $this->authorizeDoctor($consultation);

        $consultation->update(['notes' => $request->notes]);

        return response()->json([
            'message' => 'Catatan berhasil disimpan',
            'notes'   => $consultation->notes,
        ]);
    }

    public function patientsProfile(Request $request)
    {
        $doctor = $this->getDoctor();

        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 15])) {
            $perPage = 10;
        }

        // Get unique patients who have consultations with this doctor
        $patients = Patient::whereHas('consultations', function ($q) use ($doctor) {
            $q->where('doctor_id', $doctor->id);
        })->with('user')->paginate($perPage);

        return view('doctor.patients-profile', compact('patients', 'perPage'));
    }

    /**
     * Cek apakah Flask earscope sudah berjalan.
     */
    public function flaskStatus()
    {
        $flaskUrl = env('FLASK_URL', 'http://127.0.0.1:5000');

        try {
            $ch = curl_init($flaskUrl . '/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return response()->json(['running' => true, 'message' => 'Flask is running']);
            }
        } catch (\Exception $e) {
            // Flask tidak berjalan
        }

        return response()->json(['running' => false, 'message' => 'Flask is not running']);
    }

    /**
     * Menjalankan Flask earscope app di background (Windows).
     */
    public function startFlask()
    {
        $flaskUrl = env('FLASK_URL', 'http://127.0.0.1:5000');

        // Cek dulu apakah sudah running
        try {
            $ch = curl_init($flaskUrl . '/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return response()->json([
                    'status' => 'already_running',
                    'message' => 'Flask earscope sudah berjalan.',
                    'flask_url' => $flaskUrl,
                ]);
            }
        } catch (\Exception $e) {
            // Belum running, lanjut start
        }

        $flaskPath = env('FLASK_APP_PATH', '');
        $pythonPath = env('FLASK_PYTHON_PATH', 'python');

        if (!$flaskPath || !file_exists($flaskPath . DIRECTORY_SEPARATOR . 'app.py')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Path Flask app tidak ditemukan. Periksa FLASK_APP_PATH di .env',
            ], 500);
        }

        // Start Flask di background pada Windows
        // Convert forward slashes to backslashes for Windows cmd
        $winFlaskPath = str_replace('/', '\\', $flaskPath);
        $winPythonPath = str_replace('/', '\\', $pythonPath);
        $command = 'start "" /B cmd /C "cd /d "' . $winFlaskPath . '" && "' . $winPythonPath . '" app.py"';
        pclose(popen($command, 'r'));

        \Log::info('[DoctorController] Starting Flask app', [
            'command' => $command,
            'flask_path' => $flaskPath,
        ]);

        // Tunggu Flask siap (max 15 detik)
        $maxWait = 15;
        $waited = 0;
        while ($waited < $maxWait) {
            usleep(500000); // 0.5 detik
            $waited += 0.5;

            try {
                $ch = curl_init($flaskUrl . '/health');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    return response()->json([
                        'status' => 'started',
                        'message' => "Flask earscope berhasil dimulai (dalam {$waited}s).",
                        'flask_url' => $flaskUrl,
                    ]);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return response()->json([
            'status' => 'timeout',
            'message' => 'Flask dimulai tapi belum merespons setelah ' . $maxWait . ' detik. Mungkin masih loading model.',
            'flask_url' => $flaskUrl,
        ], 202);
    }
}