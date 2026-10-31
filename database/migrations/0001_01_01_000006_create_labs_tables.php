<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tables: labs, machines, lab_instances, challenges, flags, submissions
     */
    public function up(): void
    {
        // Labs table
        Schema::create('labs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('category_id')->nullable()->constrained('course_categories')->nullOnDelete();
            $table->uuid('creator_id');
            $table->string('title', 200);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'insane']);
            $table->enum('lab_type', ['challenge', 'room', 'box', 'assessment']);
            $table->integer('estimated_time_minutes')->nullable();
            $table->integer('points')->default(100);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('requires_subscription')->default(false);
            $table->enum('os_type', ['linux', 'windows', 'macos', 'multi'])->nullable();
            $table->json('tools_required')->nullable();
            $table->json('hints')->nullable();
            $table->json('learning_objectives')->nullable();
            $table->json('stats')->default('{"solves": 0, "attempts": 0, "avg_time": 0}');
            $table->json('settings')->default('{}');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('creator_id')->references('id')->on('users')->restrictOnDelete();
            $table->index('category_id');
            $table->index('creator_id');
            $table->index('difficulty');
            $table->index('lab_type');
            $table->index('is_published');
        });

        // Machines table
        Schema::create('machines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lab_id');
            $table->string('name', 100);
            $table->string('hostname', 100)->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->ipAddress('internal_ip')->nullable();
            $table->enum('os_type', ['linux', 'windows', 'macos']);
            $table->string('os_version', 100)->nullable();
            $table->integer('cpu_cores')->default(2);
            $table->integer('ram_mb')->default(2048);
            $table->integer('disk_gb')->default(20);
            $table->enum('status', ['running', 'stopped', 'building', 'error', 'maintenance'])->default('stopped');
            $table->enum('vm_type', ['vm', 'container', 'cloud'])->nullable();
            $table->string('provider', 50)->nullable();
            $table->string('provider_id', 200)->nullable();
            $table->json('credentials')->nullable();
            $table->json('network_config')->default('{}');
            $table->string('health_check_url', 500)->nullable();
            $table->json('metadata')->default('{}');
            $table->timestamps();

            $table->foreign('lab_id')->references('id')->on('labs')->cascadeOnDelete();
            $table->index('lab_id');
            $table->index('status');
        });

        // Lab instances table
        Schema::create('lab_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lab_id');
            $table->uuid('user_id');
            $table->uuid('machine_id')->nullable();
            $table->enum('status', ['pending', 'starting', 'running', 'stopped', 'expired', 'error'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->integer('reset_count')->default(0);
            $table->timestamp('last_reset_at')->nullable();
            $table->json('connection_info')->nullable();
            $table->json('usage_stats')->default('{"cpu": 0, "memory": 0, "disk": 0}');
            $table->timestamps();

            $table->foreign('lab_id')->references('id')->on('labs')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('machine_id')->references('id')->on('machines')->nullOnDelete();
            $table->index('user_id');
            $table->index('lab_id');
            $table->index('status');
        });

        // Challenges table
        Schema::create('challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lab_id');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->nullable();
            $table->integer('points')->default(100);
            $table->integer('sort_order')->default(0);
            $table->enum('challenge_type', ['web', 'crypto', 'forensics', 'reversing', 'pwn', 'misc'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('hints')->nullable();
            $table->text('solution')->nullable();
            $table->json('files')->default('[]');
            $table->json('metadata')->default('{}');
            $table->timestamps();

            $table->foreign('lab_id')->references('id')->on('labs')->cascadeOnDelete();
            $table->index('lab_id');
            $table->index('challenge_type');
        });

        // Flags table
        Schema::create('flags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('challenge_id');
            $table->string('flag_format', 50)->nullable();
            $table->string('flag_hash', 256);
            $table->boolean('is_case_sensitive')->default(false);
            $table->integer('points')->default(100);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('challenge_id')->references('id')->on('challenges')->cascadeOnDelete();
        });

        // Submissions table
        Schema::create('submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('challenge_id');
            $table->uuid('flag_id')->nullable();
            $table->string('submitted_flag', 500);
            $table->boolean('is_correct')->default(false);
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->text('feedback')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('challenge_id')->references('id')->on('challenges')->cascadeOnDelete();
            $table->foreign('flag_id')->references('id')->on('flags')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->index('user_id');
            $table->index('challenge_id');
            $table->index('is_correct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('flags');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('lab_instances');
        Schema::dropIfExists('machines');
        Schema::dropIfExists('labs');
    }
};
