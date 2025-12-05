<?php

namespace App\Enums\Computer;

enum OsEnum: int
{
    case WINDOWS = 1;
    case MACOS = 2;
    case LINUX = 3;
    case CHROME_OS = 4;
    case OTHER = 5;

    public function label(): string
    {
        return match($this) {
            self::WINDOWS => 'Windows',
            self::MACOS => 'macOS',
            self::LINUX => 'Linux',
            self::CHROME_OS => 'Chrome OS',
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
