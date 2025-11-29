<?php

namespace App\Enums\Schedule;

enum StatusEnum: int
{
    case PENDING = 1;
    case APPROVED = 2;
    case REJECTED = 3;
    case SCHEDULED = 4;
    case CANCELLED = 5;
    case COMPLETED = 6;

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Belum Terdaftar',
            self::APPROVED => 'Terdafatar',
            self::REJECTED => 'Aktif',
            self::SCHEDULED => 'Tidak Aktif',
            self::CANCELLED => 'Ditangguhkan',
            self::COMPLETED => 'Menunggu Absensi',
        };
    }
}
