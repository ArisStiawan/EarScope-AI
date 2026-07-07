<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConsultationRequest;
use App\Helpers\ActivityLogger;
use Carbon\Carbon;


class AutoCancelExpiredConsultations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consultation:auto-cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancel consultations that have passed the doctor\'s practice end time without a diagnosis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cancelled_count = 0;
        $now   = Carbon::now();
        $today = Carbon::today();

        /**
         * Cari semua konsultasi dengan status 'scheduled' yang:
         * 1. Belum memiliki diagnosis
         * 2. Tanggal jadwal sudah terlewat (hari sebelumnya), ATAU
         *    tanggal jadwal = hari ini DAN jam sekarang sudah melewati
         *    jam selesai praktik dokter yang bersangkutan
         */
        $consultations = ConsultationRequest::where('status', 'scheduled')
            ->whereDoesntHave('diagnosis')
            ->whereNotNull('scheduled_date')
            ->with('doctor')
            ->whereDate('scheduled_date', '<=', $today)
            ->get();

        foreach ($consultations as $consultation) {
            $scheduledDate = Carbon::parse($consultation->scheduled_date)->toDateString();
            $isToday       = $scheduledDate === $today->toDateString();

            if ($isToday) {
                // Jika jadwal = hari ini, cancel hanya jika sudah lewat jam selesai praktik dokter
                $doctor           = $consultation->doctor;
                $practiceEndTime  = $doctor?->practice_end_time;

                if (!$practiceEndTime) {
                    // Jika jam praktik tidak diatur, skip (tidak di-cancel hari ini)
                    continue;
                }

                // Gabungkan tanggal hari ini dengan jam selesai praktik
                $endDateTime = Carbon::parse($today->toDateString() . ' ' . $practiceEndTime);

                if ($now->lessThan($endDateTime)) {
                    // Jam praktik belum selesai → skip, belum waktunya di-cancel
                    continue;
                }
            }

            // Tanggal sudah lewat (kemarin/sebelumnya), ATAU hari ini tapi jam praktik sudah selesai
            $consultation->update(['status' => 'cancelled']);

            // Log auto-pembatalan
            ActivityLogger::logConsultationRejected($consultation, $consultation->doctor);

            $cancelled_count++;

            $this->info(
                "Cancelled consultation #{$consultation->id} " .
                "(scheduled: {$consultation->scheduled_date}) " .
                "for patient {$consultation->patient->name}"
            );
        }

        $this->info("Auto-cancel job completed. {$cancelled_count} consultation(s) cancelled.");
    }
}
