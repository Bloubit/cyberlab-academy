<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tables: quizzes, questions, answers, quiz_attempts, exam_results
     */
    public function up(): void
    {
        // Quizzes table
        Schema::create('quizzes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lesson_id')->nullable();
            $table->uuid('course_id');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('quiz_type', ['practice', 'graded', 'exam'])->default('practice');
            $table->integer('time_limit_minutes')->nullable();
            $table->decimal('passing_score', 5, 2)->default(60.00);
            $table->integer('max_attempts')->default(3);
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_answers')->default(true);
            $table->boolean('show_correct_answers')->default(true);
            $table->boolean('is_published')->default(true);
            $table->json('settings')->default('{}');
            $table->timestamps();

            $table->foreign('lesson_id')->references('id')->on('lessons')->nullOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
        });

        // Questions table
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quiz_id');
            $table->enum('question_type', ['multiple_choice', 'single_choice', 'true_false', 'fill_blank', 'code', 'essay']);
            $table->text('question_text');
            $table->text('question_html')->nullable();
            $table->text('code_snippet')->nullable();
            $table->string('code_language', 50)->nullable();
            $table->text('explanation')->nullable();
            $table->decimal('points', 5, 2)->default(1.00);
            $table->integer('sort_order')->default(0);
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'expert'])->default('medium');
            $table->json('metadata')->default('{}');
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->index('quiz_id');
        });

        // Answers table
        Schema::create('answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('question_id');
            $table->text('answer_text');
            $table->boolean('is_correct')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('match_pair_id')->nullable();
            $table->text('explanation')->nullable();
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete();
        });

        // Quiz attempts table
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('quiz_id');
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('total_points', 5, 2)->nullable();
            $table->boolean('is_passed')->default(false);
            $table->integer('time_taken_seconds')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->json('answers')->default('[]');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
        });

        // Exam results table
        Schema::create('exam_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('quiz_id');
            $table->decimal('score', 5, 2);
            $table->integer('total_questions');
            $table->integer('correct_answers');
            $table->integer('time_taken_seconds')->nullable();
            $table->boolean('is_passed');
            $table->boolean('certificate_earned')->default(false);
            $table->integer('attempt_number')->default(1);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->text('feedback')->nullable();
            $table->json('metadata')->default('{}');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quizzes');
    }
};
