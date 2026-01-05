<?php

namespace Database\Factories;

use App\Enums\AcademicClass\TypeEnum;
use App\Models\StudyProgram;
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
            'sp_id' => StudyProgram::factory(),
            'generation' => $this->faker->numberBetween(2020, 2025),
            'type' => $this->faker->randomElement(TypeEnum::cases()),
            'name' => $this->faker->word(),
            'code' => strtoupper($this->faker->bothify('???###')),
            'year' => $this->faker->numberBetween(2000, 2024),
            'semester' => $this->faker->numberBetween(1, 2),
        ];
    }
}
