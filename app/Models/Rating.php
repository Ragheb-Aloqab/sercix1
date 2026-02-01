<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Rating extends Model
{
    use HasFactory;

    protected $table = 'ratings';

    protected $fillable = [
        'order_id',
        'company_id',
        'technician_id',
        'vehicle_id',
        'speed_rating',
        'quality_rating',
        'behavior_rating',
        'overall_rating',
        'comment',
        'is_visible',
    ];

    protected $casts = [
        'speed_rating' => 'integer',
        'quality_rating' => 'integer',
        'behavior_rating' => 'integer',
        'overall_rating' => 'integer',
        'is_visible' => 'boolean',
    ];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }


    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
