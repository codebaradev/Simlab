<?php

namespace Database\Factories;

use App\Enums\FingerPrint\StatusEnum;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FingerPrint>
 */
class FingerPrintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'code' => $this->faker->unique()->bothify('FP-#######'),
            'status' => $this->faker->randomElement(StatusEnum::cases())
        ];
    }

    public function deleted()
    {
        return $this->state(function (array $attributes) {
            return [
                'deleted_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }
}
