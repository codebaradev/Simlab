<?php

namespace App\Enums;

enum UserStatusEnum: int
{
    case ACTIVE = 1;
    case SUSPEND = 2;

    /**
     * Label dalam bahasa Indonesia (untuk tampilan/UI)
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::SUSPEND => 'Suspend',
        };
    }
}
