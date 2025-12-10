<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory, SoftDeletes;

    public $tbale = "students";

    protected $fillable = [
        // 'user_id',
        'sp_id',
        'nim',
        'generation',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function study_program(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class, 'sp_id', 'id')
            ->whereHas('department');
    }

    public function academic_classes(): BelongsToMany
    {
        return $this->belongsToMany(AcademicClass::class, 'student_academic_class', 'student_id', 'academic_class_id');
    }

    public function getFirstClassAttribute()
    {
        return $this->academic_classes()
            ->orderBy('year')
            ->orderBy('semester')
            ->first();
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nim', 'like', '%' . $search . '%')
                ->orWhereHas('user', function ($u) use ($search) {
                    $u->where('username', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%');
                });
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeExcludeSameCohort($query, AcademicClass $academicClass)
    {
        return $query->whereDoesntHave('academic_classes', function ($q) use ($academicClass) {
            $q->where('year', $academicClass->year)
            ->where('semester', $academicClass->semester);
        });
    }

    public function scopeExcludeSameClass($query, $acId)
    {
        return $query->whereDoesntHave('academic_classes', function ($q) use ($acId) {
            $q->where('id', $acId);
        });
    }
}
