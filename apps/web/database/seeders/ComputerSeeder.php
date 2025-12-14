<?php

namespace Database\Seeders;

use App\Models\Computer;
use App\Models\Room;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComputerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = Room::get();

        DB::transaction(function () use ($rooms) {
            foreach ($rooms as $room) {
                Computer::factory()->count(10)->create(['room_id' => $room->id]);
            }
        });

    }
}
