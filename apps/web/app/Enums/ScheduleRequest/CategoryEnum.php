<?php

namespace App\Enums\ScheduleRequest;

enum CategoryEnum: int
{
    case COURSE = 1;
    case TRAINING = 2;
    case RESEARCH = 3;
    case EVENT = 4;
    case OTHERS = 5;

    public function label(): string
    {
        return match($this) {
            // Basic status
            self::COURSE => 'Matakuliah',
            self::TRAINING => 'Pelatihan',
            self::RESEARCH => 'Penelitian',
            self::EVENT => 'Kegiatan/Acara',
            self::OTHERS => 'Lainnya',
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

    public static function toArrayExclude(array $excludeValues = []): array
    {
        return array_map(
            fn(self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            array_filter(
                self::cases(),
                fn($case) => !in_array($case->value, $excludeValues)
            )
        );
    }
}
