<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicClass extends Model
{
    /** @use HasFactory<\Database\Factories\AcademicClassFactory> */
    use HasFactory, SoftDeletes;

    public $table = "academic_classes";

    protected $fillable = [
        // 'cl_id',
        'name',
        'code',
        'year',
        'semester',
    ];

    public function class_leader(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'cl_id', 'id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_academic_class', 'academic_class_id', 'course_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_academic_class', 'academic_class_id', 'student_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('code', 'like', '%' . $search . '%');
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
