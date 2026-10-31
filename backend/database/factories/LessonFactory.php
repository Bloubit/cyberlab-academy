<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'title' => fake()->sentence(4),
            'slug' => Str::slug(fake()->sentence(4)),
            'content_type' => fake()->randomElement(['text', 'video', 'quiz']),
            'content' => fake()->paragraphs(3, true),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_published' => true,
            'is_free' => fake()->boolean(30),
            'duration_minutes' => fake()->numberBetween(5, 60),
            'xp_reward' => fake()->numberBetween(10, 50),
        ];
    }
}
