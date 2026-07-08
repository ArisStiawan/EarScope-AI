<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom URL Cloudinary ke tabel diagnoses.
     * Kolom lama (raw_video_path, processed_video_path) tetap ada
     * untuk backward compatibility dengan data lama.
     */
    public function up(): void
    {
        Schema::table('diagnoses', function (Blueprint $table) {
            $table->string('raw_video_url')->nullable()->after('raw_video_path')
                ->comment('Cloudinary URL — bisa diputar langsung di browser (H.264)');
            $table->string('processed_video_url')->nullable()->after('processed_video_path')
                ->comment('Cloudinary URL processed video dengan bounding box YOLO');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diagnoses', function (Blueprint $table) {
            $table->dropColumn(['raw_video_url', 'processed_video_url']);
        });
    }
};
