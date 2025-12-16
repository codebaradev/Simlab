<?php

namespace App\Enums\Schedule;

use Carbon\Carbon;

enum TimeEnum: int
{
    case TIME_1 = 1;
    case TIME_2 = 2;
    case TIME_3 = 3;
    case TIME_4 = 4;
    case TIME_5 = 5;
    case TIME_6 = 6;

    public function label()
    {
        return match($this) {
            self::TIME_1 => '07.30 - 09.00',
            self::TIME_2 => '09.05 - 10.35',
            self::TIME_3 => '10.40 - 12.10',
            self::TIME_4 => '13.30 - 15.00',
            self::TIME_5 => '15.05 - 16.35',
            self::TIME_6 => '16.40 - 18.10',
        };
    }

public function startTime(): Carbon
{
    return match ($this) {
        self::TIME_1 => now()->setTime(7, 30),
        self::TIME_2 => now()->setTime(9, 5),
        self::TIME_3 => now()->setTime(10, 40),
        self::TIME_4 => now()->setTime(13, 30),
        self::TIME_5 => now()->setTime(15, 5),
        self::TIME_6 => now()->setTime(16, 40),
    };
}

public function endTime(): Carbon
{
    return match ($this) {
        self::TIME_1 => now()->setTime(9, 0),
        self::TIME_2 => now()->setTime(10, 35),
        self::TIME_3 => now()->setTime(12, 10),
        self::TIME_4 => now()->setTime(15, 0),
        self::TIME_5 => now()->setTime(16, 35),
        self::TIME_6 => now()->setTime(18, 10),
    };
}


    public static function fromNow(?Carbon $now = null): ?self
    {
        $now ??= now();

        foreach (self::cases() as $case) {
            if ($now->between(
                $case->startTime(),
                $case->endTime()
            )) {
                return $case;
            }
        }

        return null;
    }

    public static function toArray(): array
    {
        return array_map(
            fn(self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

}
