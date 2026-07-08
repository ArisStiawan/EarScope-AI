<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'birth_date',
        'age',
        'address',
        'gender',
        'medical_record_number'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->medical_record_number)) {
                $latestPatient = self::orderBy('id', 'desc')->first();
                $datePrefix = \Carbon\Carbon::now()->format('Ym');
                $nextId = $latestPatient ? $latestPatient->id + 1 : 1;
                $patient->medical_record_number = $datePrefix . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }
        
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function consultations()
    {
        return $this->hasMany(ConsultationRequest::class);
    }
}
