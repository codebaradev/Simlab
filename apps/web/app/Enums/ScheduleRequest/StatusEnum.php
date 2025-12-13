<?php

namespace App\Enums\ScheduleRequest;

enum StatusEnum: int
{
    case PENDING = 1;
    case APPROVED = 2;
    case REJECTED = 3;
    case CANCELED = 4;
    case FINISHED = 5;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Persetujuan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::CANCELED => 'Dibatalkan',
            self::FINISHED => 'Selesai',
        };
    }
}
