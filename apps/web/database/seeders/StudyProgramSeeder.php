<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\StudyProgram;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudyProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $department = Department::first();

        StudyProgram::factory()->count(10)->create([
            'department_id' => $department->id
        ]);
    }
}
