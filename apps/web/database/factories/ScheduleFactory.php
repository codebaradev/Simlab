<?php

namespace Database\Factories;

use App\Enums\Schedule\StatusEnum;
use App\Models\Course;
use App\Models\Room;
use App\Models\ScheduleRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // generate waktu yang konsisten
        $start = Carbon::instance($this->faker->dateTimeBetween('+1 days', '+10 days'))
            ->setTime($this->faker->numberBetween(7, 16), [0, 30][rand(0, 1)]);

        $end = (clone $start)->addHours($this->faker->numberBetween(1, 3));

        return [
            'room_id'        => Room::factory(),
            'sr_id'          => ScheduleRequest::factory(),
            'course_id'      => Course::factory(),

            'start_datetime' => $start,
            'end_datetime'   => $end,

            'status'         => $this->faker->randomElement(StatusEnum::cases()),

            'is_open'        => $this->faker->boolean(30), // 30% jadwal terbuka

            'building'       => $this->faker->numberBetween(1, 10),

            'information'    => $this->faker->optional()->sentence(),

        ];
    }
}
