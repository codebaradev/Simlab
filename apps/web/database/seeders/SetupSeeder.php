<?php

namespace Database\Seeders;

use App\Services\StudentService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            StudyProgramSeeder::class,
            LaboranSeeder::class,
            StudentService::class,
            LecturerSeeder::class,
        ]);
    }
}
