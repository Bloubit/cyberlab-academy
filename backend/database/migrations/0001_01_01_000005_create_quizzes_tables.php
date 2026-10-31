<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Quizzes table
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('quiz_type', 50)->default('practice');
            $table->integer('time_limit_minutes')->nullable();
            $table->decimal('passing_score', 5, 2)->default(60);
            $table->integer('max_attempts')->default(3);
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_answers')->default(true);
            $table->boolean('show_correct_answers')->default(true);
            $table->boolean('is_published')->default(true);
            $table->text('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Quiz Attempts table
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('total_points', 5, 2)->nullable();
            $table->boolean('is_passed')->default(false);
            $table->integer('time_taken_seconds')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('answers')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // Questions table
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->string('question_type', 50)->default('multiple_choice');
            $table->text('question_text');
            $table->text('question_html')->nullable();
            $table->text('code_snippet')->nullable();
            $table->string('code_language', 50)->nullable();
            $table->text('explanation')->nullable();
            $table->decimal('points', 5, 2)->default(1);
            $table->integer('sort_order')->default(0);
            $table->string('difficulty', 20)->default('medium');
            $table->text('metadata')->nullable();
            $table->timestamps();
        });

        // Answers table (for question options)
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('answer_text');
            $table->boolean('is_correct')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('match_pair_id')->nullable();
            $table->text('explanation')->nullable();
            $table->timestamps();
        });

        // Exam Results table
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->integer('total_questions');
            $table->integer('correct_answers');
            $table->integer('time_taken_seconds')->nullable();
            $table->boolean('is_passed')->default(false);
            $table->boolean('certificate_earned')->default(false);
            $table->integer('attempt_number')->default(1);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('feedback')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quizzes');
    }
};
