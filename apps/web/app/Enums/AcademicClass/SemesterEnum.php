<?php

namespace App\Enums\AcademicClass;

enum SemesterEnum: int
{
    case ODD = 1;
    case EVEN = 2;

    public function label(): string
    {
        return match($this) {
            self::ODD => 'Ganjil',
            self::EVEN => 'Genap',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ODD => 'info',
            self::EVEN => 'warning',
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
