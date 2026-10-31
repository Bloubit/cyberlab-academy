<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tables: xp_history, user_xp_summary, levels, rankings
     */
    public function up(): void
    {
        // XP history table
        Schema::create('xp_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->integer('amount');
            $table->string('source_type', 50);
            $table->uuid('source_id')->nullable();
            $table->text('description')->nullable();
            $table->decimal('multiplier', 3, 2)->default(1.00);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('user_id');
            $table->index(['source_type', 'source_id']);
            $table->index('created_at');
        });

        // User XP summary table
        Schema::create('user_xp_summary', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->integer('total_xp')->default(0);
            $table->integer('level')->default(1);
            $table->integer('xp_to_next_level')->default(100);
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // Levels table
        Schema::create('levels', function (Blueprint $table) {
            $table->integer('level')->primary();
            $table->string('title', 50);
            $table->integer('min_xp');
            $table->integer('max_xp');
            $table->string('badge_icon', 100)->nullable();
            $table->json('rewards')->default('{}');
        });

        // Rankings table
        Schema::create('rankings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ranking_type', 50);
            $table->string('period', 20)->nullable();
            $table->uuid('user_id');
            $table->integer('score');
            $table->integer('rank');
            $table->json('metadata')->default('{}');
            $table->timestamp('calculated_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['ranking_type', 'period', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rankings');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('user_xp_summary');
        Schema::dropIfExists('xp_history');
    }
};
