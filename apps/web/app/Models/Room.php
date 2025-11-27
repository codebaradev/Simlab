<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

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
