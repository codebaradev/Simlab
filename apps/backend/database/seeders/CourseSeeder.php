<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\Course;
use App\Models\Lecturer;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aclass = AcademicClass::first();
        $lecturer = Lecturer::first();

        DB::transaction(function () use ($aclass, $lecturer){
            $courseIds = Course::factory()->count(10)->create()->pluck('id');

            $aclass->courses()->attach($courseIds);
            $lecturer->courses()->attach($courseIds);
        });
    }
}
