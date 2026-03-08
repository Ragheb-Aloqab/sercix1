<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\MaintenanceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'maintenance_type',
        'description',
        'status',
        'rejection_reason',
        'requested_by_name',
        'driver_phone',
        'city',
        'address',
        'lat',
        'lng',
        'notes',
        'approved_center_id',
        'approved_quotation_id',
        'approved_quote_amount',
        'approved_by',
        'approved_at',
        'invoice_approved_by',
        'invoice_approved_at',
        'final_invoice_pdf_path',
        'final_invoice_original_name',
        'file_type',
        'final_invoice_amount',
        'final_invoice_uploaded_at',
        'final_invoice_tax_type',
        'completion_date',
        'completed_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'invoice_approved_at' => 'datetime',
        'approved_quote_amount' => 'decimal:2',
        'final_invoice_amount' => 'decimal:2',
        'final_invoice_uploaded_at' => 'datetime',
        'completion_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function approvedCenter()
    {
        return $this->belongsTo(MaintenanceCenter::class, 'approved_center_id');
    }

    public function approvedQuotation()
    {
        return $this->belongsTo(Quotation::class, 'approved_quotation_id');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function rfqAssignments()
    {
        return $this->hasMany(RfqAssignment::class);
    }

    public function assignedCenters()
    {
        return $this->belongsToMany(MaintenanceCenter::class, 'rfq_assignments')
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    public function attachments()
    {
        return $this->hasMany(MaintenanceRequestAttachment::class);
    }

    public function requestImages()
    {
        return $this->hasMany(MaintenanceRequestAttachment::class)->where('type', 'request_image');
    }

    public function afterServiceImages()
    {
        return $this->hasMany(MaintenanceRequestAttachment::class)->where('type', 'after_service_image');
    }

    public function statusLogs()
    {
        return $this->hasMany(MaintenanceRequestStatusLog::class);
    }

    public function requestServices()
    {
        return $this->hasMany(MaintenanceRequestService::class, 'maintenance_request_id')->orderBy('sort_order');
    }

    /** Whether the request has any driver-proposed services still pending approval. */
    public function hasPendingProposedServices(): bool
    {
        return $this->requestServices()
            ->whereNotNull('driver_proposed_service_id')
            ->whereHas('driverProposedService', fn ($q) => $q->where('status', DriverProposedService::STATUS_PENDING))
            ->exists();
    }

    public function approverCompany()
    {
        return $this->belongsTo(Company::class, 'approved_by');
    }

    public function invoiceApproverCompany()
    {
        return $this->belongsTo(Company::class, 'invoice_approved_by');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForDriver($query, array $phoneVariants)
    {
        return $query->whereIn('driver_phone', $phoneVariants);
    }

    public function scopeForCenter($query, int $centerId)
    {
        return $query->whereHas('rfqAssignments', fn ($q) => $q->where('maintenance_center_id', $centerId));
    }

    /** Jobs where this center was approved (completed or in progress) */
    public function scopeForApprovedCenter($query, int $centerId)
    {
        return $query->where('approved_center_id', $centerId);
    }

    public function canTransitionTo(string $to): bool
    {
        return MaintenanceRequestStatus::canTransition($this->status, $to);
    }

    public function getStatusLabelAttribute(): string
    {
        return MaintenanceRequestStatus::tryFrom($this->status)?->label() ?? $this->status;
    }

    /** Whether the final invoice file is an image (for display: thumbnail vs PDF icon). */
    public function isInvoiceImage(): bool
    {
        if ($this->file_type) {
            return $this->file_type === 'image';
        }
        $ext = strtolower(pathinfo($this->final_invoice_pdf_path ?? '', PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /** Whether the final invoice file is a PDF. */
    public function isInvoicePdf(): bool
    {
        return !$this->isInvoiceImage();
    }
}
