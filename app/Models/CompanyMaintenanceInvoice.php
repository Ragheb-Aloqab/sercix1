<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyMaintenanceInvoice extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public const FILE_TYPE_IMAGE = 'image';
    public const FILE_TYPE_PDF = 'pdf';

    public const TAX_WITHOUT = 'without_tax';
    public const TAX_WITH = 'with_tax';
    public const VAT_RATE = 0.15;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'service_type',
        'amount',
        'original_amount',
        'vat_amount',
        'tax_type',
        'invoice_file',
        'file_type',
        'original_filename',
        'description',
    ];

    /** Service type constants (category) */
    public const SERVICE_TYPE_MAINTENANCE = 'maintenance';
    public const SERVICE_TYPE_OIL_CHANGE = 'oil_change';
    public const SERVICE_TYPE_PAINTING = 'painting';
    public const SERVICE_TYPE_TIRES = 'tires';
    public const SERVICE_TYPE_OTHER = 'other';

    public static function serviceTypes(): array
    {
        return [
            self::SERVICE_TYPE_MAINTENANCE,
            self::SERVICE_TYPE_OIL_CHANGE,
            self::SERVICE_TYPE_PAINTING,
            self::SERVICE_TYPE_TIRES,
            self::SERVICE_TYPE_OTHER,
        ];
    }

    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];

    public function hasTax(): bool
    {
        return $this->tax_type === self::TAX_WITH;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'company_maintenance_invoice_service')
            ->withTimestamps();
    }

    public function isImage(): bool
    {
        return $this->invoice_file && $this->file_type === self::FILE_TYPE_IMAGE;
    }

    public function isPdf(): bool
    {
        return $this->invoice_file && $this->file_type === self::FILE_TYPE_PDF;
    }

    public function hasInvoiceFile(): bool
    {
        return !empty($this->invoice_file);
    }
}
