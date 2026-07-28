<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConsultationRequest;
use App\Models\Doctor;
use App\Helpers\ActivityLogger;

class PatientController extends Controller
{
    public function dashboard(Request $request)
    {
        $patient = auth()->user()->patient;

        // Summary stats for patient cards
        $totalRequests = $patient->consultations()->count();
        $totalDone = $patient->consultations()->where('status', 'done')->count();
        $nextScheduled = $patient->consultations()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date', 'asc')
            ->first();

        $perPage = $request->input('per_page', 5);
        if (!in_array($perPage, [5, 10, 15])) {
            $perPage = 5;
        }

        // Filter: only scheduled and pending, sorted by status (scheduled first) then by nearest date
        $activeConsultation = $patient->consultations()
            ->with('doctor.user')
            ->whereIn('status', ['scheduled', 'pending'])
            ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->first();

        return view('patient.dashboard', compact('patient', 'activeConsultation', 'totalRequests', 'totalDone', 'nextScheduled', 'perPage'));
    }

    public function createConsultation()
    {
        $patient = auth()->user()->patient;
        $hasActive = $patient->consultations()->whereIn('status', ['pending', 'scheduled'])->exists();
        if ($hasActive) {
            return redirect()->route('patient.consultation-requests');
        }

        $doctors = Doctor::all();
        return view('patient.create-consultation', compact('doctors'));
    }

    public function consultationRequests(Request $request)
    {
        $patient = auth()->user()->patient;
        $status  = $request->get('status', 'all');

        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 15])) {
            $perPage = 10;
        }

        $query = $patient->consultations()->with('doctor');

        if ($status !== 'all') {
            $query->where('status', $status)->latest();
        } else {
            // Urutan: pending → scheduled → cancelled → done
            $query->orderByRaw("CASE
                    WHEN status = 'pending'   THEN 0
                    WHEN status = 'scheduled' THEN 1
                    WHEN status = 'cancelled' THEN 2
                    WHEN status = 'done'      THEN 3
                    ELSE 4
                END")
                ->orderBy('created_at', 'desc');
        }

        $consultations = $query->paginate($perPage);

        // Cek apakah pasien boleh buat konsultasi baru
        $hasActive = $patient->consultations()
            ->whereIn('status', ['pending', 'scheduled'])
            ->exists();

        return view('patient.consultation-requests', compact('consultations', 'status', 'perPage', 'hasActive'));
    }

    public function storeConsultation(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required',
            'complaint' => 'required',
        ]);

        $patient = auth()->user()->patient;

        $hasActive = $patient->consultations()->whereIn('status', ['pending', 'scheduled'])->exists();
        if ($hasActive) {
            return redirect()->route('patient.dashboard')
                ->with('error', 'Anda masih memiliki konsultasi yang sedang berjalan.');
        }

        $consultation = ConsultationRequest::create([
            'patient_id' => $patient->id,
            'doctor_id' => $request->doctor_id,
            'complaint' => $request->complaint,
            'status' => 'pending',
        ]);
        
        // Log consultation request
        ActivityLogger::logConsultationRequested($consultation, $patient);

        return redirect()->route('patient.dashboard')
            ->with('success', 'Permintaan konsultasi berhasil dikirim! Menunggu konfirmasi dokter.');
    }

    public function consultationResults(Request $request)
    {
        $patient = auth()->user()->patient;

        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 15])) {
            $perPage = 10;
        }

        $consultations = $patient->consultations()
            ->with(['doctor', 'diagnosis'])
            ->where('status', 'done')
            ->orderBy('scheduled_date', 'desc')
            ->paginate($perPage);

        return view('patient.consultation-result', compact('consultations', 'perPage'));
    }

    public function getConsultationDetails($id)
    {
        $patient = auth()->user()->patient;
        $consultation = ConsultationRequest::with(['doctor.user', 'diagnosis.images'])->findOrFail($id);
        
        // Authorize: only patient owner can view
        if ($consultation->patient_id !== $patient->id) {
            abort(403, 'Unauthorized');
        }

        $diagnosisData = null;
        if ($consultation->diagnosis) {
            $d = $consultation->diagnosis;
            $diagnosisData = [
                'result' => $d->diagnosis_result,
                'notes' => $d->notes,
                'is_verified' => $d->is_verified,
                'raw_video_url' => $d->raw_video_url
                    ?? ($d->raw_video_path ? asset('storage/' . $d->raw_video_path) : null),
                'processed_video_url' => $d->processed_video_url
                    ?? ($d->processed_video_path ? asset('storage/' . $d->processed_video_path) : null),
                'images' => $d->images->map(fn($img) => [
                    'id' => $img->id,
                    'image_url' => asset('storage/' . $img->image_path),
                    'ai_screening_result' => $img->ai_screening_result,
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
            'doctor'         => $consultation->doctor ? [
                'id'             => $consultation->doctor->id,
                'name'           => $consultation->doctor->name,
                'email'          => $consultation->doctor->user->email ?? 'N/A',
                'practice_hours' => ($consultation->doctor->practice_start_time && $consultation->doctor->practice_end_time)
                    ? \Carbon\Carbon::parse($consultation->doctor->practice_start_time)->format('H:i')
                      . ' - '
                      . \Carbon\Carbon::parse($consultation->doctor->practice_end_time)->format('H:i')
                      . ' WIB'
                    : '-',
            ] : null,
            'diagnosis' => $diagnosisData,
        ]);
    }

    public function cancelConsultation($id)
    {
        $patient = auth()->user()->patient;
        $consultation = ConsultationRequest::findOrFail($id);
        
        // Authorize: only patient owner can cancel
        if ($consultation->patient_id !== $patient->id) {
            abort(403, 'Unauthorized');
        }

        // Only allow canceling pending or scheduled consultations
        if (!in_array($consultation->status, ['pending', 'scheduled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation cannot be cancelled in its current status.'
            ], 400);
        }

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

        // Log consultation cancellation
        ActivityLogger::logConsultationRejected($consultation, $consultation->doctor);

        return response()->json([
            'success' => true,
            'message' => 'Consultation cancelled successfully.'
        ]);
    }
}