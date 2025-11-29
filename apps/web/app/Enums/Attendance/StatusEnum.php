<?php

namespace App\Enums\Attendance;

enum StatusEnum: int
{
    case PRESENT = 1;
    case ABSENT = 2;
    case LATE = 3;
    case EXCUSED = 4;
    case SICK = 5;

    public function label(): string
    {
        return match($this) {
            self::PRESENT => 'Hadir',
            self::ABSENT => 'Tidak Hadir',
            self::LATE => 'Terlambat',
            self::EXCUSED => 'Izin',
            self::SICK  => 'Sakit',
        };
    }
}
