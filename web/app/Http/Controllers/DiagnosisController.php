<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diagnosis;
use App\Models\DiagnosisImage;
use App\Models\ConsultationRequest;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class DiagnosisController extends Controller
{
    public function index(Request $request)
    {
        $doctor = Auth::user()->doctor;

        if (!$doctor) {
            abort(403, 'Doctor not found');
        }

        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 15])) {
            $perPage = 10;
        }

        $consultations = ConsultationRequest::where('doctor_id', $doctor->id)
            ->where('status', 'scheduled')
            ->whereDoesntHave('diagnosis')
            ->with('patient')
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('queue_number', 'asc')
            ->paginate($perPage);

        return view('doctor.diagnoses', compact('consultations', 'perPage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'consultation_request_id' => 'required|exists:consultation_requests,id',
            'diagnosis_result' => 'required',
            'notes' => 'nullable',
            'image' => 'nullable|image'
        ]);

        // Gunakan updateOrCreate agar tidak duplikat jika data earscope sudah ada
        $diagnosis = Diagnosis::updateOrCreate(
            ['consultation_request_id' => $request->consultation_request_id],
            [
                'diagnosis_result' => $request->diagnosis_result,
                'notes'            => $request->notes,
            ]
        );

        // upload gambar
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('diagnosis_images', 'public');

            DiagnosisImage::updateOrCreate(
                ['diagnosis_id' => $diagnosis->id],
                ['image_path'   => $path]
            );
        }

        // Tandai konsultasi sebagai 'done'
        $consultation = $diagnosis->consultation;
        if ($consultation && $consultation->status === 'scheduled') {
            $consultation->update(['status' => 'done']);
        }

        $doctor = Auth::user()->doctor;
        if ($doctor) {
            ActivityLogger::logConsultationUploaded($diagnosis, $doctor);
        }

        return back()->with('success', 'Diagnosis berhasil disimpan');
    }
}