<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DriverNotification extends Model
{
    protected $table = 'driver_notifications';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'driver_phone',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function scopeForDriver($query, string $phone): void
    {
        $query->where('driver_phone', $phone);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
