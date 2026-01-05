<?php

namespace App\Enums\AcademicClass;

enum TypeEnum: int
{
    case GENERAL = 1;
    case SPECIALIZATION = 2;

    public function label(): string
    {
        return match($this) {
            self::GENERAL => 'Umum',
            self::SPECIALIZATION => 'Peminatan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::GENERAL => 'info',
            self::SPECIALIZATION => 'warning',
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
