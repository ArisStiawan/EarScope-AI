<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ConsultationRequest;

class Diagnosis extends Model
{
    protected $fillable = [
        'consultation_request_id',
        'diagnosis_result',
        'notes',
        'is_verified',
        'ai_result',
        'raw_video_path',
        'processed_video_path',
        'raw_video_url',
        'processed_video_url',
    ];

    public function consultationRequest()
    {
        return $this->belongsTo(ConsultationRequest::class);
    }

    /**
     * Alias for backward compatibility — some controllers still call $diagnosis->consultation
     */
    public function consultation()
    {
        return $this->belongsTo(ConsultationRequest::class, 'consultation_request_id');
    }

    public function images()
    {
        return $this->hasMany(DiagnosisImage::class);
    }

    /**
     * Memastikan URL Cloudinary yang diretrieve selalu mengandung parameter H.264
     * sehingga semua video (baik yang lama maupun yang baru) bisa diputar.
     */
    public function getRawVideoUrlAttribute($value)
    {
        if ($value && str_contains($value, 'res.cloudinary.com') && !str_contains($value, 'vc_h264')) {
            return str_replace('/upload/v', '/upload/vc_h264/v', $value);
        }
        return $value;
    }

    public function getProcessedVideoUrlAttribute($value)
    {
        if ($value && str_contains($value, 'res.cloudinary.com') && !str_contains($value, 'vc_h264')) {
            return str_replace('/upload/v', '/upload/vc_h264/v', $value);
        }
        return $value;
    }
}
