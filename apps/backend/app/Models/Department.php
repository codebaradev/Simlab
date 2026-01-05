<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use function PHPUnit\Framework\returnArgument;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    public $table = "departments";

    protected $fillable = [
        // 'head_id',
        'code',
        'name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function head(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'head_id', 'id');
    }

    public function study_program(): HasOne
    {
        return $this->hasOne(StudyProgram::class, "department_id", "id");
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', '%' . $search . '%')
                ->orWhere('name', 'like', '%' . $search . '%');
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
