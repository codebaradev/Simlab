<?php

namespace App\Enums\Schedule;

enum StatusEnum: int
{
    case PENDING = 1;
    case APPROVED = 2;
    case REJECTED = 3;
    case SCHEDULED = 4;
    case CANCELLED = 5;
    case FINISHED = 6;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::CANCELLED => 'Dibatalkan',
            self::FINISHED => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'secondary',
            self::APPROVED => 'success',
            self::REJECTED => 'error',
            self::CANCELLED => 'warning',
            self::FINISHED => 'success',
        };
    }
}
