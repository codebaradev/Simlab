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

    public function color(): string
    {
        return match($this) {
            self::PRESENT => 'success',
            self::ABSENT => 'error',
            self::LATE => 'warning',
            self::EXCUSED => 'warning',
            self::SICK  => 'warning',
        };
    }

    public function badgeColor(): string
    {
        return match($this) {
            self::PRESENT => 'badge-success',
            self::ABSENT => 'badge-error',
            self::LATE => 'badge-warning',
            self::EXCUSED => 'badge-warning',
            self::SICK  => 'badge-warning',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
