<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyFuelInvoice extends Model
{
    use HasFactory;

    public const FILE_TYPE_IMAGE = 'image';
    public const FILE_TYPE_PDF = 'pdf';

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'amount',
        'invoice_file',
        'file_type',
        'original_filename',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function isImage(): bool
    {
        return $this->file_type === self::FILE_TYPE_IMAGE;
    }

    public function isPdf(): bool
    {
        return $this->file_type === self::FILE_TYPE_PDF;
    }
}
