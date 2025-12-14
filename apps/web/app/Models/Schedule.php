<?php

namespace App\Models;

use App\Enums\Schedule\TimeEnum;
use App\Enums\ScheduleRequest\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    /** @use HasFactory<\Database\Factories\ScheduleFactory> */
    use HasFactory, SoftDeletes;

    public $table = "schedules";

    protected $fillable = [
        'room_id',
        'sr_id',
        'course_id',
        'start_date',
        // 'start_time',
        // 'end_time',
        'time',
        'status',
        'is_open',
        'building',
        'information',
    ];

    protected $casts = [
        'start_date' => 'date',
        // 'start_time' => 'time',
        // 'end_time'   => 'time',
        'time'          => TimeEnum::class,
        'is_open'        => 'boolean',
        'status'         => StatusEnum::class,
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'schedule_room', 'schedule_id', 'room_id');
    }

    public function schedule_request(): BelongsTo
    {
        return $this->belongsTo(ScheduleRequest::class, 'sr_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function attendance_monitoring(): HasOne
    {
        return $this->hasOne(AttendanceMonitoring::class, 'schedule_id', 'id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'schedule_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
