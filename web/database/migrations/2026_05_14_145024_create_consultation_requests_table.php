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
        Schema::create('consultation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->text('complaint');
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->enum('status',['pending','scheduled','cancelled','done'])->default('pending');
            $table->timestamps();
            $table->index(['patient_id','doctor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_requests');
    }
};
