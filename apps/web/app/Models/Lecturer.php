<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lecturer extends Model
{
    /** @use HasFactory<\Database\Factories\LecturerFactory> */
    use HasFactory, SoftDeletes;

    public $table = "lecturers";

    protected $fillable = [
        // 'user_id',
        'sp_id',
        'nidn',
        'nip',
        'code'
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
        return $this->belongsTo(StudyProgram::class, 'sp_id', 'id');
    }

    public function head_of_department(): HasOne
    {
        return $this->hasOne(Department::class, 'head_id', 'id');
    }

    public function head_of_sp(): HasOne
    {
        return $this->hasOne(StudyProgram::class, 'head_id', 'id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_lecturers', 'lecturer_id', 'course_id');
    }

    public function schedule_requests(): HasOne
    {
        return $this->hasOne(ScheduleRequest::class, 'user_id', 'idforeignKey: ');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', '%' . $search . '%')
                ->orWhere('nidn', 'like', '%' . $search . '%')
                ->orWhere('nip', 'like', '%' . $search . '%')
                ->orWhereHas('user', function ($u) use ($search) {
                    $u->where('username', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%');
                });;
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
