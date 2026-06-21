<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diagnosis;
use App\Models\DiagnosisImage;
use App\Models\ConsultationRequest;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DiagnosisController extends Controller
{
    public function index()
    {
        $doctor = Auth::user()->doctor;

        if (!$doctor) {
            abort(403, 'Doctor not found');
        }

        $consultations = ConsultationRequest::where('doctor_id', $doctor->id)
            ->where('status', 'scheduled')
            ->whereDoesntHave('diagnosis')
            ->with('patient')
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('scheduled_time', 'asc')
            ->paginate(10);

        return view('doctor.diagnoses', compact('consultations'));
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

    public function retake($id)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $consultation = ConsultationRequest::where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->firstOrFail();

        $diagnosis = Diagnosis::where('consultation_request_id', $consultation->id)->first();

        if ($diagnosis) {
            // Delete raw and processed video files
            if ($diagnosis->raw_video_path && Storage::disk('public')->exists($diagnosis->raw_video_path)) {
                Storage::disk('public')->delete($diagnosis->raw_video_path);
            }
            if ($diagnosis->processed_video_path && Storage::disk('public')->exists($diagnosis->processed_video_path)) {
                Storage::disk('public')->delete($diagnosis->processed_video_path);
            }

            // Delete associated diagnosis images files
            $images = DiagnosisImage::where('diagnosis_id', $diagnosis->id)->get();
            foreach ($images as $img) {
                if ($img->image_path && Storage::disk('public')->exists($img->image_path)) {
                    Storage::disk('public')->delete($img->image_path);
                }
            }
            // Delete image records
            DiagnosisImage::where('diagnosis_id', $diagnosis->id)->delete();

            // Finally delete diagnosis record
            $diagnosis->delete();
        }

        // If the consultation was accidentally marked done, revert it
        if ($consultation->status === 'done') {
            $consultation->update(['status' => 'scheduled']);
        }

        return response()->json([
            'message' => 'Data diagnosis lama telah dihapus dan siap untuk retake.',
            'status' => 'success'
        ]);
    }
}