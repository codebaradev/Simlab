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

    public static function toArray(): array
    {
        return array_map(
            fn(self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }
}
