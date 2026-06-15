<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{    
    protected $fillable = [
        'user_id',
        'name',
        'license_number',
        'specialization',
        'gender'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consultations()
    {
        return $this->hasMany(ConsultationRequest::class);
    }
}
