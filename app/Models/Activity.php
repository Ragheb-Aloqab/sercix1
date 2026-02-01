<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}