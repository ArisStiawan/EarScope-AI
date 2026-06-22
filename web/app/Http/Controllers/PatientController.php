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
        $consultations = $patient->consultations()
            ->with('doctor')
            ->whereIn('status', ['scheduled', 'pending'])
            ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('created_at', 'desc')
<<<<<<< HEAD
            ->paginate(10);
=======
            ->paginate($perPage);
>>>>>>> b339378d2604097b8b045d1e0843952bead91c98

        return view('patient.dashboard', compact('consultations', 'totalRequests', 'totalDone', 'nextScheduled', 'perPage'));
    }

    public function createConsultation()
    {
        $doctors = Doctor::all();
        return view('patient.create-consultation', compact('doctors'));
    }

    public function storeConsultation(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required',
            'complaint' => 'required',
        ]);

        $patient = auth()->user()->patient;
        $consultation = ConsultationRequest::create([
            'patient_id' => $patient->id,
            'doctor_id' => $request->doctor_id,
            'complaint' => $request->complaint,
            'status' => 'pending',
        ]);
        
        // Log consultation request
        ActivityLogger::logConsultationRequested($consultation, $patient);

        return redirect()->route('patient.dashboard')
            ->with('success', 'Consultation request submitted successfully! Waiting for doctor confirmation.');
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
            ->orderBy('scheduled_date', 'asc')
<<<<<<< HEAD
            ->paginate(10);
=======
            ->paginate($perPage);
>>>>>>> b339378d2604097b8b045d1e0843952bead91c98

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
                'raw_video_url' => $d->raw_video_path
                    ? asset('storage/' . $d->raw_video_path) : null,
                'processed_video_url' => $d->processed_video_path
                    ? asset('storage/' . $d->processed_video_path) : null,
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
            'scheduled_time' => $consultation->scheduled_time,
            'doctor'         => $consultation->doctor ? [
                'id'             => $consultation->doctor->id,
                'name'           => $consultation->doctor->name,
                'email'          => $consultation->doctor->user->email ?? 'N/A',
            ] : null,
<<<<<<< HEAD
            'diagnosis' => $diagnosisData,
=======
            'diagnosis'      => $consultation->diagnosis ? [
                'result' => $consultation->diagnosis->diagnosis_result,
                'notes'  => $consultation->diagnosis->notes,
            ] : null,
>>>>>>> b339378d2604097b8b045d1e0843952bead91c98
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

        $consultation->update(['status' => 'cancelled']);

        // Log consultation cancellation
        ActivityLogger::logConsultationRejected($consultation, $consultation->doctor);

        return response()->json([
            'success' => true,
            'message' => 'Consultation cancelled successfully.'
        ]);
    }
}