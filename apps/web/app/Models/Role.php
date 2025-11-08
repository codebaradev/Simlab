<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    public $table = "roles";

    protected $fillable = [
        'name',
        'code',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_users');
    }
}
