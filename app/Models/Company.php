<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Company extends Authenticatable
{
    use HasFactory, Notifiable;

    // لو جدولك اسمه companies فمو لازم، لكن اتركه إذا تحب
    // protected $table = 'companies';

    protected $fillable = [
        'company_name',
        'phone',
        'email',
        'status',
        // إذا عندك كلمة مرور (حتى لو OTP فقط)
        // 'password',
        'password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations (كما هي عندك)
    |--------------------------------------------------------------------------
    */
    public function branches()
    {
        return $this->hasMany(\App\Models\CompanyBranch::class);
    }
    public function services()
    {
        return $this->belongsToMany(\App\Models\Service::class, 'company_services')
            ->withPivot(['base_price', 'estimated_minutes', 'is_enabled'])
            ->withTimestamps();
    }


    public function vehicles()
    {
        return $this->hasMany(\App\Models\Vehicle::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function otpVerifications()
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }
}
