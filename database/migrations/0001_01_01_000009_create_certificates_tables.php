<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tables: certificates, certificate_verifications
     */
    public function up(): void
    {
        // Certificates table
        Schema::create('certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('course_id')->nullable();
            $table->uuid('lab_id')->nullable();
            $table->string('certificate_number', 50)->unique();
            $table->enum('certificate_type', ['course_completion', 'lab_completion', 'skill_mastery', 'special']);
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->string('pdf_url', 500)->nullable();
            $table->string('qr_code', 500)->nullable();
            $table->string('verification_url', 500)->nullable();
            $table->json('metadata')->default('{}');
            $table->boolean('revoked')->default(false);
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoked_reason')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
            $table->foreign('lab_id')->references('id')->on('labs')->nullOnDelete();
            $table->index('user_id');
        });

        // Certificate verifications table
        Schema::create('certificate_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('certificate_id');
            $table->uuid('verified_by')->nullable();
            $table->enum('verification_type', ['qr_scan', 'manual', 'api', 'link'])->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('verified_at')->useCurrent();
            $table->enum('result', ['valid', 'invalid', 'revoked'])->nullable();

            $table->foreign('certificate_id')->references('id')->on('certificates')->cascadeOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_verifications');
        Schema::dropIfExists('certificates');
    }
};
