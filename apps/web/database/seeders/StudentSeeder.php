<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\StudyProgram;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sp = StudyProgram::where('code', 'IK')->first();

        if (!$sp) {
            $sp = StudyProgram::factory()->create(['code' => "IK", 'name' => 'Ilmu Komputer']);
        }

        DB::transaction(function () use ($sp)  {
            Student::factory()->count(10)->create(['sp_id' => $sp->id]);
        });

    }
}
