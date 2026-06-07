<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Level extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'code'
    ];

    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }
}
