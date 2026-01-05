<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfid extends Model
{
    /** @use HasFactory<\Database\Factories\RfidFactory> */
    use HasFactory, SoftDeletes;

    public $table = "rfids";

    protected $fillable = [
        'code',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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
