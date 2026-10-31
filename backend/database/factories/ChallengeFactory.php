<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\Lab;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeFactory extends Factory
{
    protected $model = Challenge::class;

    public function definition(): array
    {
        return [
            'lab_id' => Lab::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'hint' => fake()->sentence(),
            'points' => fake()->numberBetween(25, 200),
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
