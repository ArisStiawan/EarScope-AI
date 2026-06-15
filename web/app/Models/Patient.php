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
        'email',
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
