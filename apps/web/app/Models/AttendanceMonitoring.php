<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceMonitoring extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceMonitoringFactory> */
    use HasFactory, SoftDeletes;

    public $table = "attendance_monitorings";

    protected $fillable = [
        'schedule_id',
        'topic',
        'sub_topic'
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
