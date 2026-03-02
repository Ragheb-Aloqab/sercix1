<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfqAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_request_id',
        'maintenance_center_id',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function maintenanceCenter()
    {
        return $this->belongsTo(MaintenanceCenter::class);
    }

    public function assignerCompany()
    {
        return $this->belongsTo(Company::class, 'assigned_by');
    }
}
