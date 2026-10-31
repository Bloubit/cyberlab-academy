<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'instructor_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'description' => fake()->paragraph(),
            'difficulty_level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'price' => fake()->randomFloat(2, 0, 199),
            'is_free' => fake()->boolean(70),
            'is_published' => true,
            'is_featured' => false,
            'requirements' => ['Basic computer knowledge'],
            'learning_outcomes' => ['Understand cybersecurity concepts'],
            'tags' => ['security'],
            'stats' => ['enrollments' => 0, 'completions' => 0],
            'settings' => [],
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_published' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function free(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_free' => true,
            'price' => 0,
        ]);
    }
}
