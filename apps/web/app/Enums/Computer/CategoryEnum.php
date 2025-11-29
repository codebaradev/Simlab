<?php

namespace App\Enums\Computer;

enum CategoryEnum: int
{
    case HIGH_SPEC = 1;
    case MEDIUM_SPEC = 2;
    case LOW_SPEC = 3;

    public function label(): string
    {
        return match($this) {
            self::HIGH_SPEC => 'High Specification',
            self::MEDIUM_SPEC => 'Medium Specification',
            self::LOW_SPEC => 'Low Specification',
        };
    }
}
