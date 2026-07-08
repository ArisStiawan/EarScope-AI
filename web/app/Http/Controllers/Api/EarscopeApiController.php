<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Diagnosis;
use App\Models\DiagnosisImage;
use App\Models\ConsultationRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class EarscopeApiController extends Controller
{
    /**
     * Inisialisasi Cloudinary SDK dengan konfigurasi dari .env
     */
    private function getCloudinary(): Cloudinary
    {
        return new Cloudinary(
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
                'url' => ['secure' => true],
            ])
        );
    }

    /**
     * Upload sebuah file video ke Cloudinary.
     * Mengembalikan secure URL hasil upload, atau null jika gagal.
     */
    private function uploadVideoToCloudinary(string $filePath, string $folder): ?string
    {
        try {
            $cloudinary = $this->getCloudinary();
            $result = $cloudinary->uploadApi()->upload($filePath, [
                'resource_type' => 'video',
                'folder'        => $folder,
                'eager'         => [
                    // Transcoding otomatis ke H.264 agar kompatibel dengan browser
                    ['format' => 'mp4', 'video_codec' => 'h264'],
                ],
                'eager_async'  => false, // Tunggu transcoding selesai
            ]);

            // Ambil URL hasil transcoding H.264 dari eager array
            $url = $result['eager'][0]['secure_url'] ?? ($result['secure_url'] ?? null);
            Log::info('[Cloudinary] Upload sukses', ['url' => $url, 'folder' => $folder]);
            return $url;

        } catch (\Exception $e) {
            Log::error('[Cloudinary] Upload gagal: ' . $e->getMessage(), ['folder' => $folder]);
            return null;
        }
    }

    /**
     * Menerima hasil diagnosa dari Flask App (Earscope).
     * Dipanggil secara otomatis setelah stop recording di perangkat earscope.
     *
     * Body: consultation_id (form-data), hasil_diagnosis (form-data),
     *       raw_video (file), processed_video (file)
     */
    public function receive(Request $request)
    {
        Log::info('[Earscope API] Incoming request', [
            'consultation_id' => $request->consultation_id,
            'hasil_diagnosis'  => $request->hasil_diagnosis,
            'has_raw'          => $request->hasFile('raw_video'),
            'has_processed'    => $request->hasFile('processed_video'),
        ]);

        // --- Validasi ---
        $validated = $request->validate([
            'consultation_id' => 'required|exists:consultation_requests,id',
            'hasil_diagnosis'  => 'required|string|max:255',
            'raw_video'        => 'nullable|file|mimes:mp4,avi,mov,mkv,webm|max:204800', // max 200MB
            'processed_video'  => 'nullable|file|mimes:mp4,avi,mov,mkv,webm|max:204800',
        ]);

        $consultationId = $validated['consultation_id'];
        $hasilDiagnosis = $validated['hasil_diagnosis'];

        // --- Pastikan konsultasi berstatus 'scheduled' ---
        $consultation = ConsultationRequest::find($consultationId);
        if (!$consultation || $consultation->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Konsultasi tidak ditemukan atau statusnya bukan scheduled.',
            ], 422);
        }

        // --- Upload video ke Cloudinary ---
        $rawVideoUrl       = null;
        $processedVideoUrl = null;
        $rawPath           = null;
        $processedPath     = null;

        if ($request->hasFile('raw_video')) {
            $file        = $request->file('raw_video');
            $folder      = "earscope/{$consultationId}/raw";

            // Upload ke Cloudinary
            $rawVideoUrl = $this->uploadVideoToCloudinary($file->getRealPath(), $folder);

            // Simpan juga ke storage lokal sebagai backup
            $rawPath = $file->store("earscope_videos/{$consultationId}/raw", 'public');
        }

        if ($request->hasFile('processed_video')) {
            $file        = $request->file('processed_video');
            $folder      = "earscope/{$consultationId}/processed";

            // Upload ke Cloudinary
            $processedVideoUrl = $this->uploadVideoToCloudinary($file->getRealPath(), $folder);

            // Simpan juga ke storage lokal sebagai backup
            $processedPath = $file->store("earscope_videos/{$consultationId}/processed", 'public');
        }

        // --- Buat atau update record Diagnosis ---
        $diagnosis = Diagnosis::updateOrCreate(
            ['consultation_request_id' => $consultationId],
            [
                'ai_result'             => $hasilDiagnosis,
                'raw_video_path'        => $rawPath,
                'processed_video_path'  => $processedPath,
                'raw_video_url'         => $rawVideoUrl,
                'processed_video_url'   => $processedVideoUrl,
                // Isi diagnosis_result dengan ai_result sebagai default agar field not-null terpenuhi.
                // Dokter masih bisa mengedit/menimpa lewat form web.
                'diagnosis_result'      => $hasilDiagnosis,
            ]
        );

        Log::info('[Earscope API] Diagnosis saved', [
            'diagnosis_id'      => $diagnosis->id,
            'raw_video_url'     => $rawVideoUrl ?? '(tidak ada)',
            'processed_video_url' => $processedVideoUrl ?? '(tidak ada)',
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'Data earscope berhasil diterima.',
            'diagnosis_id' => $diagnosis->id,
        ], 201);
    }

    /**
     * Menerima foto capture dari Flask App (Earscope).
     * Dipanggil saat dokter menekan tombol "Ambil Foto" di antarmuka Flask.
     *
     * Body: consultation_id (form-data), raw_image (file), bbox_image (file),
     *       ai_screening_result (form-data, string)
     */
    public function uploadPhoto(Request $request)
    {
        Log::info('[Earscope API] Upload photo request', [
            'consultation_id'     => $request->consultation_id,
            'ai_screening_result' => $request->ai_screening_result,
            'has_raw_image'       => $request->hasFile('raw_image'),
            'has_bbox_image'      => $request->hasFile('bbox_image'),
        ]);

        $request->validate([
            'consultation_id'     => 'required|exists:consultation_requests,id',
            'raw_image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480',
            'bbox_image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480',
            'ai_screening_result' => 'nullable|string|max:500',
        ]);

        $consultationId = $request->consultation_id;

        // Buat atau cari record Diagnosis untuk konsultasi ini
        $diagnosis = Diagnosis::firstOrCreate(
            ['consultation_request_id' => $consultationId],
            [
                'diagnosis_result' => 'Menunggu diagnosis dokter',
                'ai_result'        => $request->ai_screening_result ?? null,
            ]
        );

        $savedImages = [];

        // Simpan raw image (foto tetap di storage lokal — JPG sudah kompatibel browser)
        if ($request->hasFile('raw_image')) {
            $rawPath = $request->file('raw_image')
                ->store("earscope_photos/{$consultationId}/raw", 'public');

            $rawRecord = DiagnosisImage::create([
                'diagnosis_id'        => $diagnosis->id,
                'image_path'          => $rawPath,
                'ai_screening_result' => ['type' => 'raw', 'label' => 'Foto mentah (tanpa deteksi)'],
            ]);
            $savedImages[] = $rawRecord;
        }

        // Simpan bbox image (dengan bounding box deteksi YOLO)
        if ($request->hasFile('bbox_image')) {
            $bboxPath = $request->file('bbox_image')
                ->store("earscope_photos/{$consultationId}/bbox", 'public');

            $bboxRecord = DiagnosisImage::create([
                'diagnosis_id'        => $diagnosis->id,
                'image_path'          => $bboxPath,
                'ai_screening_result' => [
                    'type'  => 'bbox',
                    'label' => 'Foto dengan deteksi AI',
                    'result' => $request->ai_screening_result ?? null,
                ],
            ]);
            $savedImages[] = $bboxRecord;
        }

        Log::info('[Earscope API] Photos saved', [
            'diagnosis_id' => $diagnosis->id,
            'photos_count' => count($savedImages),
        ]);

        return response()->json([
            'success'       => true,
            'message'       => count($savedImages) . ' foto berhasil disimpan.',
            'diagnosis_id'  => $diagnosis->id,
            'photos_saved'  => count($savedImages),
        ], 201);
    }

    /**
     * Mengembalikan data earscope terbaru untuk sebuah konsultasi.
     * Di-polling dari halaman diagnosa dokter.
     */
    public function latest(Request $request)
    {
        $consultationId = $request->query('consultation_id');

        if (!$consultationId) {
            return response()->json(['success' => false, 'message' => 'consultation_id diperlukan.'], 400);
        }

        $diagnosis = Diagnosis::where('consultation_request_id', $consultationId)
            ->with('images')
            ->first();

        if (!$diagnosis || !$diagnosis->ai_result) {
            // Cek apakah ada foto meskipun belum ada ai_result (dari capture)
            if ($diagnosis && $diagnosis->images->count() > 0) {
                return response()->json([
                    'success'             => true,
                    'ai_result'           => $diagnosis->ai_result ?? null,
                    'raw_video_url'       => null,
                    'processed_video_url' => null,
                    'photos'              => $diagnosis->images->map(fn($img) => [
                        'id'                  => $img->id,
                        'image_url'           => Storage::disk('public')->url($img->image_path),
                        'ai_screening_result' => $img->ai_screening_result,
                        'created_at'          => $img->created_at->toISOString(),
                    ]),
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Belum ada data earscope.'], 404);
        }

        return response()->json([
            'success'             => true,
            'ai_result'           => $diagnosis->ai_result,
            // Prioritaskan URL Cloudinary; fallback ke storage lokal jika belum ada
            'raw_video_url'       => $diagnosis->raw_video_url
                ?? ($diagnosis->raw_video_path ? Storage::disk('public')->url($diagnosis->raw_video_path) : null),
            'processed_video_url' => $diagnosis->processed_video_url
                ?? ($diagnosis->processed_video_path ? Storage::disk('public')->url($diagnosis->processed_video_path) : null),
            'photos'              => $diagnosis->images->map(fn($img) => [
                'id'                  => $img->id,
                'image_url'           => Storage::disk('public')->url($img->image_path),
                'ai_screening_result' => $img->ai_screening_result,
                'created_at'          => $img->created_at->toISOString(),
            ]),
        ]);
    }
}
