<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'sks' => $this->faker->numberBetween(1, 6),
            'year' => $this->faker->numberBetween(2000, 2024),
            'semester' => $this->faker->numberBetween(1, 2),
        ];
    }
}
