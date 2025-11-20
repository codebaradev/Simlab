<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->regexify('DEPT[A-Z0-9]{3}'),
            'name' => $this->faker->words(2, true) . ' Department',
        ];
    }

    public function deleted(): Factory
    {
        return $this->state(function (array $attributes) {
            return [];
        })->afterCreating(function (Department $department) {
            $department->delete();
        });
    }
}
