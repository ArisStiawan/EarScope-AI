<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-cancel konsultasi yang sudah melewati tanggal jadwal tanpa diagnosis.
// Dijalankan setiap menit — command akan otomatis skip konsultasi yang
// jam praktik dokternya belum selesai, sehingga pembatalan terjadi
// tepat saat jam praktik masing-masing dokter habis.
Schedule::command('consultation:auto-cancel')
    ->everyMinute()
    ->withoutOverlapping();
