<?php

namespace Database\Factories;

use App\Enums\ScheduleRequest\CategoryEnum;
use App\Enums\ScheduleRequest\StatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduleRequest>
 */
class ScheduleRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(StatusEnum::cases()),
            'category' => $this->faker->randomElement(CategoryEnum::cases()),
            'information' => $this->faker->word()
        ];
    }
}
