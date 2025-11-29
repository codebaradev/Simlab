<?php

namespace App\Enums\Computer;

enum OsEnum: int
{
    case WINDOWS_10 = 1;
    case WINDOWS_11 = 2;
    case WINDOWS_SERVER = 3;
    case UBUNTU = 4;
    case DEBIAN = 5;
    case CENTOS = 6;
    case MACOS = 7;
    case CHROME_OS = 8;
    case OTHER = 9;

    public function label(): string
    {
        return match($this) {
            self::WINDOWS_10 => 'Windows 10',
            self::WINDOWS_11 => 'Windows 11',
            self::WINDOWS_SERVER => 'Windows Server',
            self::UBUNTU => 'Ubuntu',
            self::DEBIAN => 'Debian',
            self::CENTOS => 'CentOS',
            self::MACOS => 'macOS',
            self::CHROME_OS => 'Chrome OS',
            self::OTHER => 'Lainnya',
        };
    }

    public function family(): string
    {
        return match($this) {
            self::WINDOWS_10, self::WINDOWS_11, self::WINDOWS_SERVER => 'Windows',
            self::UBUNTU, self::DEBIAN, self::CENTOS => 'Linux',
            self::MACOS => 'macOS',
            self::CHROME_OS => 'Chrome OS',
            self::OTHER => 'Lainnya',
        };
    }
}
