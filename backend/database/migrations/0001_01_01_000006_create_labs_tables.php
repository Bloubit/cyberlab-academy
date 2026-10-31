<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Labs table
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('difficulty_level', 50)->default('beginner');
            $table->string('type', 50)->default('single_machine');
            $table->integer('duration_minutes')->nullable();
            $table->integer('xp_reward')->default(100);
            $table->text('learning_objectives')->nullable();
            $table->text('hints')->nullable();
            $table->text('network_topology')->nullable();
            $table->boolean('is_published')->default(false);
            $table->text('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Machines table
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('image', 500);
            $table->string('os', 50)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('credentials')->nullable();
            $table->text('ports')->nullable();
            $table->text('services')->nullable();
            $table->integer('cpu_cores')->default(2);
            $table->integer('ram_mb')->default(2048);
            $table->integer('disk_gb')->default(20);
            $table->text('provisioning_script')->nullable();
            $table->timestamps();
        });

        // Lab Instances table
        Schema::create('lab_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->string('container_id')->nullable();
            $table->text('connection_info')->nullable();
            $table->text('resources')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->timestamps();
        });

        // Challenges table
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('hint')->nullable();
            $table->integer('points')->default(50);
            $table->integer('sort_order')->default(0);
            $table->text('metadata')->nullable();
            $table->timestamps();
        });

        // Flags table
        Schema::create('flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->string('flag_hash', 255);
            $table->string('flag_type', 50)->default('static');
            $table->text('validation_rules')->nullable();
            $table->integer('attempts_allowed')->nullable();
            $table->timestamps();
        });

        // Submissions table
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flag_id')->nullable()->constrained()->nullOnDelete();
            $table->string('submitted_flag');
            $table->boolean('is_correct')->default(false);
            $table->integer('points_earned')->default(0);
            $table->integer('attempt_number')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

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
