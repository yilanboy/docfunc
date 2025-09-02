<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Passkey extends Model
{
    /** @use HasFactory<\Database\Factories\PasskeyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'credential_id',
        'data',
        'last_used_at',
    ];

    protected $casts = [
        'data'         => 'json',
        'last_used_at' => 'datetime',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
