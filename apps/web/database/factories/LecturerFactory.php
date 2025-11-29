<?php

namespace Database\Factories;

use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lecturer>
 */
class LecturerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->lecturer(),
            'sp_id' => StudyProgram::factory(),
            'code' => $this->faker->unique()->regexify('DEPT[A-Z0-9]{3}'),
            'nip' => $this->faker->unique()->numerify('##########'),
            'nidn' => $this->faker->unique()->numerify('##########'),
        ];
    }
}
