<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequestStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_request_id',
        'from_status',
        'to_status',
        'note',
        'actor_type',
        'actor_id',
    ];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }
}
