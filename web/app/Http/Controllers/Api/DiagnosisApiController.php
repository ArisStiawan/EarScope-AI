<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\Diagnosis;
use App\Models\DiagnosisImage;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DiagnosisApiController extends Controller
{
    /**
     * Get list of active consultations that are approved and ready for examination.
     */
    public function getActiveConsultations()
    {
        // Get approved consultation requests
        $consultations = ConsultationRequest::where('status', 'approved')
            ->with(['patient', 'doctor'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'count' => $consultations->count(),
            'data' => $consultations->map(function ($c) {
                return [
                    'id' => $c->id,
                    'patient' => [
                        'id' => $c->patient->id,
                        'name' => $c->patient->name,
                        'age' => $c->patient->age,
                        'gender' => $c->patient->gender,
                    ],
                    'doctor' => [
                        'id' => $c->doctor->id,
                        'name' => $c->doctor->name,
                    ],
                    'complaint' => $c->complaint,
                    'scheduled_date' => $c->scheduled_date,
                    'scheduled_time' => $c->scheduled_time,
                ];
            })
        ]);
    }

    /**
     * Store diagnosis screening from Jetson device.
     */
    public function storeDiagnosis(Request $request, $consultation_id)
    {
        $request->validate([
            'diagnosis_result' => 'required|string|max:255',
            'ai_screening_result' => 'nullable|string', // JSON string from Jetson
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // max 10MB
        ]);

        $consultation = ConsultationRequest::with('patient')->findOrFail($consultation_id);

        if (!in_array($consultation->status, ['approved', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation is not in approved or pending status.'
            ], 400);
        }

        // Store image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('diagnoses', 'public');
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No image file uploaded.'
            ], 400);
        }

        // Check if diagnosis already exists for this consultation (if re-uploaded)
        $diagnosis = Diagnosis::where('consultation_request_id', $consultation_id)->first();

        if ($diagnosis) {
            $diagnosis->update([
                'diagnosis_result' => $request->diagnosis_result,
                'is_verified' => false, // reset verification on re-upload
            ]);
        } else {
            $diagnosis = Diagnosis::create([
                'consultation_request_id' => $consultation_id,
                'diagnosis_result' => $request->diagnosis_result,
                'is_verified' => false,
            ]);
        }

        // Decode metadata AI
        $aiScreeningResult = null;
        if ($request->ai_screening_result) {
            $aiScreeningResult = json_decode($request->ai_screening_result, true);
        }

        // Create Diagnosis Image record
        DiagnosisImage::create([
            'diagnosis_id' => $diagnosis->id,
            'image_path' => $path,
            'ai_screening_result' => $aiScreeningResult,
        ]);

        // Log system activity
        ActivityLogger::log(
            'jetson_uploaded',
            "Perangkat Jetson mengunggah hasil diagnosis AI awal untuk pasien '{$consultation->patient->name}'",
            [
                'consultation_id' => $consultation->id,
                'diagnosis_id' => $diagnosis->id,
                'diagnosis_result' => $request->diagnosis_result,
                'image_path' => $path,
            ],
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => 'AI Screening diagnosis uploaded successfully. Awaiting doctor verification.',
            'data' => [
                'diagnosis_id' => $diagnosis->id,
                'is_verified' => $diagnosis->is_verified,
            ]
        ], 201);
    }
}
