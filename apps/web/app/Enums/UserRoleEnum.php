<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case ADMIN = 'adm';
    case KEPALA_LAB = 'kpl';
    case LABORAN = 'lbr';
    case DOSEN = 'dsn';
    case MAHASISWA = 'mhs';

    /**
     * Label dalam bahasa Indonesia (untuk tampilan/UI)
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::KEPALA_LAB => 'Kepala Laboratorium',
            self::LABORAN => 'Laboran',
            self::DOSEN => 'Dosen',
            self::MAHASISWA => 'Mahasiswa',
        };
    }

    /**
     * Nama dalam bahasa Inggris (opsional untuk penggunaan internal)
     */
    public function english(): string
    {
        return match ($this) {
            self::ADMIN => 'ADMIN',
            self::KEPALA_LAB => 'Head of Laboratory',
            self::LABORAN => 'Laboran',
            self::DOSEN => 'Lecturer',
            self::MAHASISWA => 'Student',
        };
    }

    /**
     * Mengambil enum berdasarkan kode (misal: 'adm', 'dsn', 'mhs')
     */
    public static function fromCode(string $code): ?self
    {
        return collect(self::cases())
            ->first(fn ($case) => $case->value === $code);
    }
}
