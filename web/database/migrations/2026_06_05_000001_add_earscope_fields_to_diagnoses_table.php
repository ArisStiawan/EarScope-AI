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
        Schema::table('diagnoses', function (Blueprint $table) {
            $table->string('ai_result')->nullable()->after('diagnosis_result');
            $table->string('raw_video_path')->nullable()->after('ai_result');
            $table->string('processed_video_path')->nullable()->after('raw_video_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diagnoses', function (Blueprint $table) {
            $table->dropColumn(['ai_result', 'raw_video_path', 'processed_video_path']);
        });
    }
};
