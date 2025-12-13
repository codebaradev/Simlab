<?php

namespace Database\Seeders;

use App\Services\StudentService;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->call([
                RoleSeeder::class,
                DepartmentSeeder::class,
                StudyProgramSeeder::class,
                LaboranSeeder::class,
                StudentService::class,
                LecturerSeeder::class,
            ]);
        });
    }
}
