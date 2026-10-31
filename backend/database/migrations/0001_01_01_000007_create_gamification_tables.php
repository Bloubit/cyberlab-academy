<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // XP History table
        Schema::create('xp_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');
            $table->string('source_type', 50);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('description')->nullable();
            $table->decimal('multiplier', 5, 2)->default(1.00);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['source_type', 'source_id']);
        });

        // Levels table
        Schema::create('levels', function (Blueprint $table) {
            $table->integer('level')->primary();
            $table->string('title');
            $table->bigInteger('min_xp');
            $table->bigInteger('max_xp');
            $table->string('badge_icon', 100)->nullable();
            $table->text('rewards')->nullable();
            $table->timestamps();
        });

        // Rankings table
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->string('ranking_type', 50)->default('global');
            $table->string('period', 20)->default('all_time');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('score')->default(0);
            $table->integer('rank')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['ranking_type', 'period', 'user_id']);
            $table->index(['period', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rankings');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('xp_history');
    }
};
