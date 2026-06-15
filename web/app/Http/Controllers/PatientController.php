<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConsultationRequest;
use App\Models\Doctor;
use App\Helpers\ActivityLogger;

class PatientController extends Controller
{
    public function dashboard()
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

        // Filter: only scheduled and pending, sorted by status (scheduled first) then by nearest date
        $consultations = $patient->consultations()
            ->with('doctor')
            ->whereIn('status', ['scheduled', 'pending'])
            ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('patient.dashboard', compact('consultations', 'totalRequests', 'totalDone', 'nextScheduled'));
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
            ->with('success', 'Konsultasi berhasil dikirim! Menunggu konfirmasi dari dokter.');
    }

    public function consultationResults()
    {
        $patient = auth()->user()->patient;

        $consultations = $patient->consultations()
            ->with(['doctor', 'diagnosis'])
            ->where('status', 'done')
            ->orderBy('scheduled_date', 'asc')
            ->get();

        return view('patient.consultation-result', compact('consultations'));
    }

    public function getConsultationDetails($id)
    {
        $patient = auth()->user()->patient;
        $consultation = ConsultationRequest::with('doctor.user')->findOrFail($id);
        
        // Authorize: only patient owner can view
        if ($consultation->patient_id !== $patient->id) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'id' => $consultation->id,
            'complaint' => $consultation->complaint,
            'status' => $consultation->status,
            'created_at' => $consultation->created_at,
            'scheduled_date' => $consultation->scheduled_date,
            'scheduled_time' => $consultation->scheduled_time,
            'doctor' => $consultation->doctor ? [
                'id' => $consultation->doctor->id,
                'name' => $consultation->doctor->name,
                'specialization' => $consultation->doctor->specialization ?? 'ENT Specialist',
                'email' => $consultation->doctor->user->email ?? 'N/A'
            ] : null,
            'diagnosis' => $consultation->diagnosis ? [
                'result' => $consultation->diagnosis->diagnosis_result,
                'notes' => $consultation->diagnosis->notes,
            ] : null,
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