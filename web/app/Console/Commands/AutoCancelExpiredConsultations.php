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
    protected $description = 'Automatically cancel consultations whose scheduled date has passed without a diagnosis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cancelled_count = 0;
        $today = Carbon::today();

        /**
         * Cari semua konsultasi dengan status 'scheduled' yang:
         * 1. Belum memiliki diagnosis
         * 2. Tanggal jadwal sudah terlewat (scheduled_date < hari ini)
         *
         * Artinya: jika jadwal = 18 Juni dan hari ini sudah 19 Juni ke atas,
         * maka konsultasi otomatis dibatalkan karena tidak ada diagnosis.
         */
        $consultations = ConsultationRequest::where('status', 'scheduled')
            ->whereDoesntHave('diagnosis')
            ->whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '<', $today)
            ->get();

        foreach ($consultations as $consultation) {
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
