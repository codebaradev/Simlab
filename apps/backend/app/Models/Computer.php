<?php

namespace App\Models;

use App\Enums\Computer\CategoryEnum;
use App\Enums\Computer\DisplayResolutionEnum;
use App\Enums\Computer\OsEnum;
use App\Enums\Computer\RamTypeEnum;
use App\Enums\Computer\StorageTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Computer extends Model
{
    /** @use HasFactory<\Database\Factories\ComputerFactory> */
    use HasFactory, SoftDeletes;

    public $table = "computers";

    protected $fillable = [
        'room_id',
        'computer_count',
        'name', // searchable
        'processor',
        'gpu',
        'ram_capacity',
        'ram_type', // enum
        'storage_capacity',
        'storage_type', // enum
        'display_size',
        'display_resolution', // enum
        'display_refresh_rate',
        'os', // enum
        'release_year',
        'category', // enum
    ];

    protected $casts = [
        'ram_type' => RamTypeEnum::class,
        'storage_type' => StorageTypeEnum::class,
        'display_resolution' => DisplayResolutionEnum::class,
        'os' => OsEnum::class,
        'category' => CategoryEnum::class

    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%');
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
