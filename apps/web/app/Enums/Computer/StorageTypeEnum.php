<?php

namespace App\Enums\Computer;

enum StorageTypeEnum: int
{
    case HDD = 1;
    case SSD_SATA = 2;
    case SSD_NVME = 3;
    case SSD_M2 = 4;
    case SSD_PCIE = 5;
    case EMMC = 6;
    case OTHER = 7;

    public function label(): string
    {
        return match($this) {
            self::HDD => 'HDD',
            self::SSD_SATA => 'SSD SATA',
            self::SSD_NVME => 'SSD NVMe',
            self::SSD_M2 => 'SSD M.2',
            self::SSD_PCIE => 'SSD PCIe',
            self::EMMC => 'eMMC',
            self::OTHER => 'Lainnya',
        };
    }
}
