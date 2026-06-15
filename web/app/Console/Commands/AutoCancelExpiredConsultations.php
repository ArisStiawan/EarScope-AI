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
    protected $description = 'Automatically cancel consultations that have not been diagnosed within 24 hours of scheduled time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cancelled_count = 0;

        // Find all scheduled consultations without diagnosis and older than 24 hours
        $consultations = ConsultationRequest::where('status', 'scheduled')
            ->whereDoesntHave('diagnosis')
            ->whereNotNull('scheduled_date')
            ->whereNotNull('scheduled_time')
            ->get();

        foreach ($consultations as $consultation) {
            // Combine scheduled_date and scheduled_time
            $scheduledDateTime = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $consultation->scheduled_date . ' ' . $consultation->scheduled_time
            );

            // Add 24 hours
            $expiryDateTime = $scheduledDateTime->addHours(24);

            // Check if we're past the expiry time
            if (Carbon::now() > $expiryDateTime) {
                $consultation->update(['status' => 'cancelled']);

                // Log the auto-cancellation
                ActivityLogger::logConsultationRejected($consultation, $consultation->doctor);
                
                $cancelled_count++;

                $this->info("Cancelled consultation #{$consultation->id} for patient {$consultation->patient->name}");
            }
        }

        $this->info("Auto-cancel job completed. {$cancelled_count} consultation(s) cancelled.");
    }
}
