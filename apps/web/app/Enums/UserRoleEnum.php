<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case ADMIN = 'adm';
    case LAB_HEAD = 'kpl';
    case LABORAN = 'lbr';
    case LECTURER = 'dsn';
    case STUDENT = 'mhs';

    /**
     * Label dalam bahasa Indonesia (untuk tampilan/UI)
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::LAB_HEAD => 'Kepala Laboratorium',
            self::LABORAN => 'Laboran',
            self::LECTURER => 'Dosen',
            self::STUDENT => 'Mahasiswa',
        };
    }

    /**
     * Nama dalam bahasa Inggris (opsional untuk penggunaan internal)
     */
    public function english(): string
    {
        return match ($this) {
            self::ADMIN => 'ADMIN',
            self::LAB_HEAD => 'Head of Laboratory',
            self::LABORAN => 'Laboran',
            self::LECTURER => 'Lecturer',
            self::STUDENT => 'Student',
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
