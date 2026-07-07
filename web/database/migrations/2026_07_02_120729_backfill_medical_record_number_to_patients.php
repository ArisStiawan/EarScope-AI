<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $patients = \App\Models\Patient::all();
        foreach ($patients as $patient) {
            if (empty($patient->medical_record_number)) {
                $datePrefix = \Carbon\Carbon::parse($patient->created_at)->format('Ym');
                $patient->medical_record_number = 'RM-' . $datePrefix . '-' . str_pad($patient->id, 3, '0', STR_PAD_LEFT);
                $patient->saveQuietly();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            //
        });
    }
};
