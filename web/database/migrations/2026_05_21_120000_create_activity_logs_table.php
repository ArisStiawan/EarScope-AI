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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('activity_type', [
                'user_registered',
                'doctor_login',
                'consultation_requested',
                'consultation_approved',
                'consultation_rejected',
                'consultation_uploaded'
            ]);
            $table->string('description');
            $table->json('data')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            // Index untuk query yang lebih cepat
            $table->index(['user_id', 'created_at']);
            $table->index('activity_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
