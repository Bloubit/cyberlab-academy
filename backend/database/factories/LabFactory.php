<?php

namespace Database\Factories;

use App\Models\Lab;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LabFactory extends Factory
{
    protected $model = Lab::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'difficulty_level' => fake()->randomElement(['beginner', 'intermediate', 'advanced', 'expert']),
            'type' => fake()->randomElement(['single_machine', 'multi_machine', 'challenge']),
            'duration_minutes' => fake()->numberBetween(15, 180),
            'xp_reward' => fake()->numberBetween(50, 500),
            'is_published' => true,
        ];
    }

    public function published(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_published' => true,
        ]);
    }

    public function beginner(): static
    {
        return $this->state(fn(array $attributes) => [
            'difficulty_level' => 'beginner',
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_published' => false,
        ]);
    }
}
