<?php

namespace App\Enums\FingerPrint;

enum StatusEnum: int
{
    // Status dasar fingerprint
    case NOT_REGISTERED = 0;
    case REGISTERED = 1;
    case ACTIVE = 2;
    case INACTIVE = 3;
    case SUSPENDED = 4;

    // Status khusus absensi
    case ATTENDANCE_PENDING = 5;
    case ATTENDANCE_VERIFIED = 6;
    case ATTENDANCE_REJECTED = 7;

    public function label(): string
    {
        return match($this) {
            // Basic status
            self::NOT_REGISTERED => 'Belum Terdaftar',
            self::REGISTERED => 'Terdafatar',
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Tidak Aktif',
            self::SUSPENDED => 'Ditangguhkan',

            // Attendance specific
            self::ATTENDANCE_PENDING => 'Menunggu Absensi',
            self::ATTENDANCE_VERIFIED => 'Absensi Terverifikasi',
            self::ATTENDANCE_REJECTED => 'Absensi Ditolak',
        };
    }
}
