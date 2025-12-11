<?php

namespace App\Models;

use App\Enums\AcademicClass\SemesterEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory, SoftDeletes;

    public $table = "courses";

    protected $fillable = [
        'name',
        'sks',
        'year',
        'semester',
    ];

    protected $casts = [
        'semester' => SemesterEnum::class,
    ];

    public function lecturers(): BelongsToMany
    {
        return $this->belongsToMany(Lecturer::class, 'course_lecturers', 'course_id', 'lecturer_id');
    }

    public function academic_classes(): BelongsToMany
    {
        return $this->belongsToMany(AcademicClass::class, 'course_academic_class', 'course_id', 'academic_class_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%');
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
