<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class School extends Model
{
    use HasUuids;

    protected $fillable = [
        'school_name',
        'school_code',
        'address',
        'contact_email',
        'contact_phone'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function uploadLogs(): HasMany
    {
        return $this->hasMany(UploadLog::class);
    }
}
