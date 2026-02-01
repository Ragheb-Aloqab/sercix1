<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'bank_name',
        'account_name',
        'iban',
        'account_number',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function makeDefault(int $id): void
    {
        static::query()->update(['is_default' => false]);
        static::query()->whereKey($id)->update(['is_default' => true]);
    }
}
