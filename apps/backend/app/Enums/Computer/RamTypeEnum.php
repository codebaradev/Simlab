<?php

namespace App\Enums\Computer;

enum RamTypeEnum: int
{
    case DDR3 = 1;
    case DDR4 = 2;
    case DDR5 = 3;
    case LPDDR4 = 4;
    case LPDDR5 = 5;
    case OTHER = 6;

    public function label(): string
    {
        return match($this) {
            self::DDR3 => 'DDR3',
            self::DDR4 => 'DDR4',
            self::DDR5 => 'DDR5',
            self::LPDDR4 => 'LPDDR4',
            self::LPDDR5 => 'LPDDR5',
            self::OTHER => 'Lainnya',
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
