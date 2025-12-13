<?php

namespace Database\Seeders;

use App\Enums\RoomStatusEnum;
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
        $rooms = [
            [
                'name' => 'Lab-201',
                'code' => '201',
            ],
            [
                'name' => 'Lab-202',
                'code' => '202',
            ],
            [
                'name' => 'Lab-203',
                'code' => '203',
            ],
            [
                'name' => 'Lab-204',
                'code' => '204',
            ],
            [
                'name' => 'Lab-205',
                'code' => '205',
            ],
        ];

        DB::transaction(function () use ($rooms) {
            foreach ($rooms as $room) {
                Room::create([
                    'status' => RoomStatusEnum::AVAILABLE->value,
                    'name' => $room['name'],
                    'code' => $room['code'],
                ]);
            }
        });
    }
}
