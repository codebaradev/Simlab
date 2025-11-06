<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (UserRoleEnum::cases() as $roleEnum) {
            Role::updateOrCreate(
                ['code' => $roleEnum->value],
                ['label' => $roleEnum->label()]
            );
        }
    }
}
