<?php

namespace App\Enums\Computer;

enum DisplayResolutionEnum: int
{
    case HD_1366x768 = 1;
    case HD_PLUS_1600x900 = 2;
    case FHD_1920x1080 = 3;
    case QHD_2560x1440 = 4;
    case UHD_4K_3840x2160 = 5;
    case RETINA_2880x1800 = 6;
    case RETINA_3024x1964 = 7;
    case OTHER = 8;

    public function label(): string
    {
        return match($this) {
            self::HD_1366x768 => 'HD (1366×768)',
            self::HD_PLUS_1600x900 => 'HD+ (1600×900)',
            self::FHD_1920x1080 => 'Full HD (1920×1080)',
            self::QHD_2560x1440 => 'QHD (2560×1440)',
            self::UHD_4K_3840x2160 => '4K UHD (3840×2160)',
            self::RETINA_2880x1800 => 'Retina (2880×1800)',
            self::RETINA_3024x1964 => 'Retina (3024×1964)',
            self::OTHER => 'Lainnya',
        };
    }

    public function width(): int
    {
        return match($this) {
            self::HD_1366x768 => 1366,
            self::HD_PLUS_1600x900 => 1600,
            self::FHD_1920x1080 => 1920,
            self::QHD_2560x1440 => 2560,
            self::UHD_4K_3840x2160 => 3840,
            self::RETINA_2880x1800 => 2880,
            self::RETINA_3024x1964 => 3024,
            self::OTHER => 0,
        };
    }

    public function height(): int
    {
        return match($this) {
            self::HD_1366x768 => 768,
            self::HD_PLUS_1600x900 => 900,
            self::FHD_1920x1080 => 1080,
            self::QHD_2560x1440 => 1440,
            self::UHD_4K_3840x2160 => 2160,
            self::RETINA_2880x1800 => 1800,
            self::RETINA_3024x1964 => 1964,
            self::OTHER => 0,
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
