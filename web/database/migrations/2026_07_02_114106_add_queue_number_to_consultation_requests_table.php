<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->integer('queue_number')->nullable()->after('scheduled_date');
        });

        // Populate existing records
        $consultations = DB::table('consultation_requests')
            ->whereNotNull('scheduled_date')
            ->whereIn('status', ['scheduled', 'done'])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();
        
        $queues = [];
        foreach ($consultations as $c) {
            $key = $c->doctor_id . '_' . $c->scheduled_date;
            if (!isset($queues[$key])) {
                $queues[$key] = 1;
            } else {
                $queues[$key]++;
            }
            DB::table('consultation_requests')
                ->where('id', $c->id)
                ->update(['queue_number' => $queues[$key]]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->dropColumn('queue_number');
        });
    }
};
