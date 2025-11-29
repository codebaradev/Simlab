<?php

namespace App\Models;

use App\Enums\FingerPrint\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FingerPrint extends Model
{
    /** @use HasFactory<\Database\Factories\FingerPrintFactory> */
    use HasFactory, SoftDeletes;

    public $table = "finger_prints";

    protected $fillable = [
        'room_id',
        'code',
        'status',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', '%' . $search . '%');
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
