<?php

namespace Database\Seeders;

use App\Enums\UserStatusEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $role = Role::where('code', 'adm')->first();

            $user = User::create([
                'name' => "admin",
                'username' => "admin",
                'password' => Hash::make('admin123'),
                'status' => UserStatusEnum::ACTIVE->value,
            ]);

            $user->roles()->attach($role->id);
        });


    }
}
