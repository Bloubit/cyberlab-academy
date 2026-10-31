<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tables: notifications, user_activity_log, course_analytics, lab_analytics
     */
    public function up(): void
    {
        // Notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('type', 50);
            $table->string('title', 200);
            $table->text('message');
            $table->json('data')->default('{}');
            $table->timestamp('read_at')->nullable();
            $table->string('action_url', 500)->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('channel', ['in_app', 'email', 'push', 'sms'])->default('in_app');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('user_id');
            $table->index('read_at');
            $table->index('type');
        });

        // User activity log table
        Schema::create('user_activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('user_id')->nullable();
            $table->string('action', 100);
            $table->string('entity_type', 50)->nullable();
            $table->uuid('entity_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->default('{}');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('user_id');
            $table->index('action');
            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });

        // Course analytics table
        Schema::create('course_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('course_id');
            $table->date('date');
            $table->integer('views')->default(0);
            $table->integer('enrollments')->default(0);
            $table->integer('completions')->default(0);
            $table->decimal('avg_time_minutes', 10, 2)->nullable();
            $table->decimal('drop_off_rate', 5, 2)->nullable();

            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            $table->unique(['course_id', 'date']);
        });

        // Lab analytics table
        Schema::create('lab_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lab_id');
            $table->date('date');
            $table->integer('starts')->default(0);
            $table->integer('completions')->default(0);
            $table->decimal('avg_time_minutes', 10, 2)->nullable();
            $table->decimal('success_rate', 5, 2)->nullable();

            $table->foreign('lab_id')->references('id')->on('labs')->cascadeOnDelete();
            $table->unique(['lab_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_analytics');
        Schema::dropIfExists('course_analytics');
        Schema::dropIfExists('user_activity_log');
        Schema::dropIfExists('notifications');
    }
};
