<?php

namespace App\Models;

use App\Enums\RoomStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use function PHPUnit\Framework\returnArgument;

class Room extends Model
{
    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory, SoftDeletes;

    public $table = "rooms";

    protected $fillable = [
        'status',
        'name',
        'code',
    ];

    protected $casts = [
        'status' => RoomStatusEnum::class,
    ];

    public function computers(): HasMany
    {
        return $this->hasMany(Computer::class, 'room_id', 'id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'room_id', 'id');
    }

    public function finger_prints(): HasOne
    {
        return $this->hasOne(FingerPrint::class, 'room_id', 'id');
    }

    // public function schedules(): HasMany
    // {
    //     return $this->hasMany(Schedule::class, 'room_id', 'id');
    // }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'schedule_room', 'room_id', 'schedule_id');
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
