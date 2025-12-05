<?php

namespace Database\Seeders;

use App\Models\Room;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            Room::factory()->count(10)->create();
        });
    }
}
