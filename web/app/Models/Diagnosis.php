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
}
