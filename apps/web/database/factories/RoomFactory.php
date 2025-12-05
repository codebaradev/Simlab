<?php

namespace Database\Factories;

use App\Enums\RoomStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => $this->faker->randomElement(RoomStatusEnum::values()),
            'name' => $this->faker->word(),
            'code' => $this->faker->unique()->bothify('ROOM-####'),
        ];
    }
}
