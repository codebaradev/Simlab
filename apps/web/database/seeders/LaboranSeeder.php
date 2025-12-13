<?php

namespace Database\Seeders;

use App\Models\User;
use DB;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaboranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            User::factory()->laboran()->create([
                'name' => 'laboran1',
                'username' => 'laboran1',
                'email' => 'laboran@gmail.com',
                'password' => Hash::make('123'),
            ]);
        });
    }
}
