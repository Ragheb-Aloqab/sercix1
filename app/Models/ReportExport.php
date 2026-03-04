<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReportExport extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'type',
        'file_path',
        'filename',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
