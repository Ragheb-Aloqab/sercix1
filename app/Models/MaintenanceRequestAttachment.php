<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequestAttachment extends Model
{
    use HasFactory;

    public const TYPE_REQUEST_IMAGE = 'request_image';
    public const TYPE_AFTER_SERVICE_IMAGE = 'after_service_image';

    protected $fillable = [
        'maintenance_request_id',
        'type',
        'file_path',
        'original_name',
        'file_size',
        'uploaded_by_center_id',
    ];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function uploadedByCenter()
    {
        return $this->belongsTo(MaintenanceCenter::class, 'uploaded_by_center_id');
    }
}
