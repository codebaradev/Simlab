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
}
