<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosisImage extends Model
{
    protected $fillable = [
        'diagnosis_id',
        'image_path',
        'ai_screening_result'
    ];

    protected $casts = [
        'ai_screening_result' => 'array'
    ];

    public function diagnosis()
    {
        return $this->belongsTo(Diagnosis::class);
    }
}
