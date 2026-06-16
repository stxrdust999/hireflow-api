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
        Schema::create('applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_id');
            $table->uuid('current_stage_id')->nullable();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->foreign('job_id')->references('id')->on('job_openings')->cascadeOnDelete();
            $table->foreign('current_stage_id')->references('id')->on('job_stages')->nullOnDelete();
            $table->string('resume_url')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'approved', 'rejected', 'withdrawn'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
