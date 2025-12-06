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

    public static function toArray(): array
    {
        return array_map(
            fn(self $case) => [
                'value' => $case->value,
                'la\bel' => $case->label(),
            ],
            self::cases()
        );
    }
}
