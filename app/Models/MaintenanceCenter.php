<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Notifications\Notifiable;

class MaintenanceCenter extends Model implements AuthenticatableContract
{
    use HasFactory, Authenticatable, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'city',
        'is_active',
        'status',
        'theme_preference',
        'service_categories',
        'total_earnings',
        'paid_amount',
        'pending_payments',
        'total_completed_jobs',
        'rating',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'service_categories' => 'array',
        'total_earnings' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'pending_payments' => 'decimal:2',
        'rating' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_maintenance_center')
            ->withTimestamps();
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function rfqAssignments()
    {
        return $this->hasMany(RfqAssignment::class);
    }

    public function approvedMaintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'approved_center_id');
    }
}
