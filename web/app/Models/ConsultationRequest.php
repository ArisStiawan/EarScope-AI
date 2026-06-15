<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationRequest extends Model
{
        protected $fillable = [
        'patient_id',
        'doctor_id',
        'complaint',
        'status',
        'scheduled_date',
        'scheduled_time'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function diagnosis()
    {
        return $this->hasOne(Diagnosis::class);
    }
}
