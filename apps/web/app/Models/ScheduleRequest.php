<?php

namespace App\Models;

use App\Enums\ScheduleRequest\CategoryEnum;
use App\Enums\ScheduleRequest\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleRequest extends Model
{
    /** @use HasFactory<\Database\Factories\ScheduleRequestFactory> */
    use HasFactory, SoftDeletes;

    public $table = 'schedule_requests';

    protected $fillable = [
        'user_id',
        'lecturer_id',
        'repeat_count',
        'status',
        'category',
        'information'
    ];

    protected $casts = [
        'status' => StatusEnum::class,
        'category' => CategoryEnum::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'sr_id', 'id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->whereHas('user', function ($q2) use ($search) {
                $q2->where('username', 'like' , '%' . $search . '%');
            });
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
