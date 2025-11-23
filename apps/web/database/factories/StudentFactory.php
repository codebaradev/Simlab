<?php

namespace Database\Factories;

use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'sp_id' => StudyProgram::factory(),
            'nim' => $this->faker->unique()->numerify('#########'),
            'generation' => $this->faker->year(),
        ];
    }
}
