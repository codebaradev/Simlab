<?php

namespace App\Enums\Schedule;

enum BuildingEnum: int
{
    case CAMPUS_1 = 1;
    case CAMPUS_2_R = 2;
    case CAMPUS_2_LT = 3;

    public function label(): string
    {
        return match($this) {
            self::CAMPUS_1 => 'Kampus 1',
            self::CAMPUS_2_R => 'Kampus 2',
            self::CAMPUS_2_LT => 'Kampus 2 Lab Terpadu',
        };
    }
}
