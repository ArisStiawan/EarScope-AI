<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{    
    protected $fillable = [
        'user_id',
        'name',
        'license_number',
        'gender',
        'practice_start_time',
        'practice_end_time',
        'patient_quota'
    ];

    /**
     * Flag sementara (bukan kolom DB) untuk menandai bahwa admin
     * mengisi kuota secara manual, sehingga boot() tidak override.
     * Dideklarasikan sebagai public property agar tidak masuk ke
     * Eloquent $attributes dan tidak ikut di-INSERT/UPDATE ke database.
     */
    public bool $manual_quota = false;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($doctor) {
            // Skip auto-calc jika admin mengisi kuota secara manual
            if (!empty($doctor->manual_quota)) {
                return;
            }

            if ($doctor->practice_start_time && $doctor->practice_end_time) {
                $start = \Carbon\Carbon::parse($doctor->practice_start_time);
                $end   = \Carbon\Carbon::parse($doctor->practice_end_time);

                // If end time is next day (e.g. 23:00 to 02:00)
                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                $diffInMinutes = $start->diffInMinutes($end);
                $hours = $diffInMinutes / 60;

                // Calculate quota based on 3 patients per hour (dibulatkan ke bawah)
                $doctor->patient_quota = floor($hours * 3);
            } else {
                $doctor->patient_quota = 0;
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
