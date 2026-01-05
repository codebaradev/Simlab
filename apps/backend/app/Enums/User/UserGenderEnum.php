<?php

namespace App\Enums\User;

enum UserGenderEnum: int
{
    case MALE = 1;
    case FEMALE = 2;

    /**
     * Get label for gender
     */
    public function label(): string
    {
        return match($this) {
            self::MALE => 'Laki-laki',
            self::FEMALE => 'Perempuan',
        };
    }

    /**
     * Get abbreviation for gender
     */
    public function abbreviation(): string
    {
        return match($this) {
            self::MALE => 'L',
            self::FEMALE => 'P',
        };
    }

    public static function toArray(): array
    {
        return array_map(
            fn(self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'abbreviation' => $case->abbreviation(),
            ],
            self::cases()
        );
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }
}
