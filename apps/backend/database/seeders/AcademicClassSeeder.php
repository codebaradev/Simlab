<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\StudyProgram;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sp = StudyProgram::where('code', 'IK')->first();

        DB::transaction(function () use ($sp) {
            AcademicClass::factory()->count(10)->create(['sp_id' => $sp->id]);
        });
    }
}
