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
            self::PENDING => 'Pending',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::CANCELED => 'Dibatalkan',
            self::FINISHED => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'status-secondary',
            self::APPROVED => 'status-success',
            self::REJECTED => 'status-error',
            self::CANCELED => 'status-warning',
            self::FINISHED => 'status-success',
        };
    }
}
