<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicClass>
 */
class AcademicClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'code' => strtoupper($this->faker->bothify('???###')),
            'year' => $this->faker->numberBetween(2000, 2024),
            'semester' => $this->faker->numberBetween(1, 2),
        ];
    }
}
