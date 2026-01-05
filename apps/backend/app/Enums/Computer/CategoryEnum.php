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
            self::HIGH_SPEC => 'Tinggi',
            self::MEDIUM_SPEC => 'Menengah',
            self::LOW_SPEC => 'Rendah',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::HIGH_SPEC => 'error',
            self::MEDIUM_SPEC => 'warning',
            self::LOW_SPEC => 'info',
        };
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
