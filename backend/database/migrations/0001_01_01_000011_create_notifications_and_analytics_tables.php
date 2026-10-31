<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('title');
            $table->text('message')->nullable();
            $table->text('data')->nullable();
            $table->string('action_url', 500)->nullable();
            $table->string('priority', 20)->default('normal');
            $table->string('channel', 20)->default('in_app');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at', 'created_at']);
        });

        // User Activity Log table
        Schema::create('user_activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 100);
            $table->string('entity_type', 50)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
        });

        // Course Analytics table
        Schema::create('course_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('total_enrollments')->default(0);
            $table->integer('new_enrollments')->default(0);
            $table->integer('active_students')->default(0);
            $table->integer('completions')->default(0);
            $table->decimal('avg_rating', 3, 1)->nullable();
            $table->integer('total_reviews')->default(0);
            $table->text('metrics')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'date']);
        });

        // Lab Analytics table
        Schema::create('lab_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('total_starts')->default(0);
            $table->integer('total_completions')->default(0);
            $table->decimal('avg_completion_time', 10, 2)->nullable();
            $table->decimal('avg_score', 5, 2)->nullable();
            $table->text('metrics')->nullable();
            $table->timestamps();

            $table->unique(['lab_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_analytics');
        Schema::dropIfExists('course_analytics');
        Schema::dropIfExists('user_activity_log');
        Schema::dropIfExists('notifications');
    }
};
