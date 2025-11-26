<?php

namespace App\Enums;

enum RoomStatusEnum: int
{
    case AVAILABLE = 1;
    case OCCUPIED = 2;
    case RESERVED = 3;
    case MAINTENANCE = 4;
    case CLEANING = 5;
    case UNAVAILABLE = 6;

    public function label(): string
    {
        return match($this) {
            self::AVAILABLE => 'Tersedia',
            self::OCCUPIED => 'Sedang Digunakan',
            self::RESERVED => 'Telah Dipesan',
            self::MAINTENANCE => 'Dalam Perawatan',
            self::CLEANING => 'Sedang Dibersihkan',
            self::UNAVAILABLE => 'Tidak Tersedia',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::AVAILABLE => 'success',
            self::OCCUPIED => 'danger',
            self::RESERVED => 'warning',
            self::MAINTENANCE => 'info',
            self::CLEANING => 'primary',
            self::UNAVAILABLE => 'secondary',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::AVAILABLE => 'fa-check-circle',
            self::OCCUPIED => 'fa-users',
            self::RESERVED => 'fa-calendar-check',
            self::MAINTENANCE => 'fa-tools',
            self::CLEANING => 'fa-broom',
            self::UNAVAILABLE => 'fa-times-circle',
        };
    }
}
