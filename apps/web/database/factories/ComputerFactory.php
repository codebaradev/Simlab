<?php

namespace Database\Factories;

use App\Enums\Computer\CategoryEnum;
use App\Enums\Computer\DisplayResolutionEnum;
use App\Enums\Computer\OsEnum;
use App\Enums\Computer\RamTypeEnum;
use App\Enums\Computer\StorageTypeEnum;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Computer>
 */
class ComputerFactory extends Factory
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
            'computer_count' => $this->faker->numberBetween(1, 5),
            'name' => $this->faker->words(3, true) . ' Computer',
            'processor' => $this->faker->randomElement([
                'Intel Core i5-12400',
                'Intel Core i7-12700K',
                'AMD Ryzen 5 5600X',
                'AMD Ryzen 7 5800X',
                'Apple M1 Pro',
                'Apple M2',
            ]),
            'gpu' => $this->faker->randomElement([
                'NVIDIA GeForce RTX 3060',
                'NVIDIA GeForce RTX 4070',
                'AMD Radeon RX 6700 XT',
                'Intel Iris Xe Graphics',
                'Apple M2 GPU',
                'Integrated Graphics',
            ]),
            'ram_capacity' => $this->faker->randomElement([4, 8, 16, 32, 64]),
            'ram_type' => $this->faker->randomElement(RamTypeEnum::cases()),
            'storage_capacity' => $this->faker->randomElement([256, 512, 1024, 2048]),
            'storage_type' => $this->faker->randomElement(StorageTypeEnum::cases()),
            'display_size' => $this->faker->randomFloat(1, 13, 32),
            'display_resolution' => $this->faker->randomElement(DisplayResolutionEnum::cases()),
            'display_refresh_rate' => $this->faker->randomElement([60, 75, 120, 144, 165, 240]),
            'os' => $this->faker->randomElement(OsEnum::cases()),
            'release_year' => $this->faker->numberBetween(2018, 2024),
            'category' => $this->faker->randomElement(CategoryEnum::cases()),
        ];
    }

    public function deleted(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'deleted_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }
}
