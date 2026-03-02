<?php

namespace App\Services;

use App\Enums\MaintenanceRequestStatus;
use App\Models\Company;
use App\Models\MaintenanceCenter;
use App\Models\MaintenanceRequest;
use App\Models\Quotation;
use App\Models\RfqAssignment;
use Illuminate\Support\Facades\DB;

class MaintenanceRfqService
{
    /**
     * Reject maintenance request (manager action).
     */
    public function rejectRequest(MaintenanceRequest $request, string $rejectionReason): void
    {
        $this->transitionStatus($request, MaintenanceRequestStatus::REJECTED->value, [
            'rejection_reason' => $rejectionReason,
        ], 'company', auth('company')->id());

        app(\App\Services\DriverNotificationService::class)->notifyMaintenanceRequestRejected($request);
    }

    /**
     * Approve & send RFQ to maintenance centers (manager action).
     * Option A: Specific center IDs (only active centers).
     * Option B: Broadcast to all active centers (pass broadcast=true, centerIds can be empty).
     */
    public function sendRfq(MaintenanceRequest $request, array $centerIds, bool $broadcast = false): void
    {
        $company = auth('company')->user();

        if ($broadcast) {
            $validCenterIds = MaintenanceCenter::active()->pluck('id')->toArray();
        } else {
            if (empty($centerIds)) {
                throw new \InvalidArgumentException('At least one maintenance center must be selected, or use broadcast.');
            }
            $validCenterIds = MaintenanceCenter::active()
                ->whereIn('id', $centerIds)
                ->pluck('id')
                ->toArray();
            if (count($validCenterIds) !== count(array_unique($centerIds))) {
                throw new \InvalidArgumentException('One or more selected centers are not active or do not exist.');
            }
        }

        if (empty($validCenterIds)) {
            throw new \InvalidArgumentException('No active maintenance centers available.');
        }

        DB::transaction(function () use ($request, $validCenterIds, $company) {
            $this->transitionStatus($request, MaintenanceRequestStatus::WAITING_FOR_QUOTES->value, [], 'company', $company->id);

            foreach ($validCenterIds as $centerId) {
                RfqAssignment::firstOrCreate(
                    [
                        'maintenance_request_id' => $request->id,
                        'maintenance_center_id' => $centerId,
                    ],
                    [
                        'assigned_by' => $company->id,
                        'assigned_at' => now(),
                    ]
                );

                $center = MaintenanceCenter::find($centerId);
                if ($center) {
                    $center->notify(new \App\Notifications\RfqAssignedNotification($request));
                }
            }
        });
    }

    /**
     * Submit quotation (maintenance center action).
     */
    public function submitQuotation(MaintenanceRequest $request, MaintenanceCenter $center, array $data): Quotation
    {
        $assignment = RfqAssignment::where('maintenance_request_id', $request->id)
            ->where('maintenance_center_id', $center->id)
            ->firstOrFail();

        if ($request->status !== MaintenanceRequestStatus::WAITING_FOR_QUOTES->value) {
            throw new \InvalidArgumentException('This request is no longer accepting quotations.');
        }

        return DB::transaction(function () use ($request, $center, $data) {
            $quotation = Quotation::updateOrCreate(
                [
                    'maintenance_request_id' => $request->id,
                    'maintenance_center_id' => $center->id,
                ],
                [
                    'price' => $data['price'],
                    'estimated_duration_minutes' => $data['estimated_duration_minutes'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'quotation_pdf_path' => $data['quotation_pdf_path'] ?? null,
                    'original_pdf_name' => $data['original_pdf_name'] ?? null,
                    'submitted_by' => $center->id,
                    'submitted_at' => now(),
                ]
            );

            $request->refresh();
            if ($request->status === MaintenanceRequestStatus::WAITING_FOR_QUOTES->value) {
                $this->transitionStatus($request, MaintenanceRequestStatus::QUOTE_SUBMITTED->value, [], 'maintenance_center', $center->id);
            }

            $request->company?->notify(new \App\Notifications\MaintenanceQuotationSubmittedNotification($request, $center));

            return $quotation;
        });
    }

    /**
     * Approve one center (manager action).
     */
    public function approveCenter(MaintenanceRequest $request, int $quotationId): void
    {
        $quotation = Quotation::where('maintenance_request_id', $request->id)
            ->where('id', $quotationId)
            ->whereNotNull('submitted_at')
            ->firstOrFail();

        $company = auth('company')->user();
        if ((int) $request->company_id !== (int) $company->id) {
            throw new \InvalidArgumentException('Unauthorized.');
        }

        if (!in_array($request->status, [MaintenanceRequestStatus::QUOTE_SUBMITTED->value], true)) {
            throw new \InvalidArgumentException('Request is not in quote comparison phase.');
        }

        DB::transaction(function () use ($request, $quotation, $company) {
            $request->update([
                'approved_center_id' => $quotation->maintenance_center_id,
                'approved_quotation_id' => $quotation->id,
                'approved_quote_amount' => $quotation->price,
                'approved_by' => $company->id,
                'approved_at' => now(),
            ]);
            $this->transitionStatus($request, MaintenanceRequestStatus::CENTER_APPROVED->value, [], 'company', $company->id);

            $center = $quotation->maintenanceCenter;
            if ($center) {
                $center->notify(new \App\Notifications\MaintenanceCenterApprovedNotification($request));
            }
            app(\App\Services\DriverNotificationService::class)->notifyMaintenanceCenterApproved($request);
        });
    }

    /**
     * Reject all quotes and re-request (manager action).
     */
    public function rejectAllAndReRequest(MaintenanceRequest $request, array $centerIds): void
    {
        $company = auth('company')->user();
        if ((int) $request->company_id !== (int) $company->id) {
            throw new \InvalidArgumentException('Unauthorized.');
        }

        if ($request->status !== MaintenanceRequestStatus::QUOTE_SUBMITTED->value) {
            throw new \InvalidArgumentException('Request is not in quote comparison phase.');
        }

        DB::transaction(function () use ($request, $centerIds, $company) {
            Quotation::where('maintenance_request_id', $request->id)->delete();
            RfqAssignment::where('maintenance_request_id', $request->id)->delete();

            $this->transitionStatus($request, MaintenanceRequestStatus::WAITING_FOR_QUOTES->value, [], 'company', $company->id);

            if (!empty($centerIds)) {
                $validCenterIds = MaintenanceCenter::active()
                    ->whereIn('id', $centerIds)
                    ->pluck('id')
                    ->toArray();
                foreach ($validCenterIds as $centerId) {
                    RfqAssignment::create([
                        'maintenance_request_id' => $request->id,
                        'maintenance_center_id' => $centerId,
                        'assigned_by' => $company->id,
                        'assigned_at' => now(),
                    ]);

                    $center = MaintenanceCenter::find($centerId);
                    if ($center) {
                        $center->notify(new \App\Notifications\RfqAssignedNotification($request));
                    }
                }
            }
        });
    }

    /**
     * Mark job as started (center action).
     */
    public function markStarted(MaintenanceRequest $request, MaintenanceCenter $center): void
    {
        $this->ensureCenterApproved($request, $center);

        if ($request->status !== MaintenanceRequestStatus::CENTER_APPROVED->value) {
            throw new \InvalidArgumentException('Request must be in center approved state.');
        }

        $this->transitionStatus($request, MaintenanceRequestStatus::IN_PROGRESS->value, [], 'maintenance_center', $center->id);

        $request->company?->notify(new \App\Notifications\MaintenanceJobStartedNotification($request));
    }

    /**
     * Upload final invoice and mark waiting for approval (center action).
     */
    public function uploadFinalInvoice(MaintenanceRequest $request, MaintenanceCenter $center, array $data): void
    {
        $this->ensureCenterApproved($request, $center);

        if ($request->status !== MaintenanceRequestStatus::IN_PROGRESS->value) {
            throw new \InvalidArgumentException('Job must be in progress.');
        }

        DB::transaction(function () use ($request, $center, $data) {
            $request->update([
                'final_invoice_pdf_path' => $data['final_invoice_pdf_path'],
                'final_invoice_original_name' => $data['final_invoice_original_name'] ?? null,
                'final_invoice_amount' => $data['final_invoice_amount'] ?? null,
                'final_invoice_uploaded_at' => now(),
                'completion_date' => $data['completion_date'] ?? null,
            ]);
            $this->transitionStatus($request, MaintenanceRequestStatus::WAITING_FOR_INVOICE_APPROVAL->value, [], 'maintenance_center', $center->id);

            $request->company?->notify(new \App\Notifications\MaintenanceInvoiceUploadedNotification($request));
        });
    }

    /**
     * Approve invoice and close (manager action).
     */
    public function approveInvoice(MaintenanceRequest $request): void
    {
        $company = auth('company')->user();
        if ((int) $request->company_id !== (int) $company->id) {
            throw new \InvalidArgumentException('Unauthorized.');
        }

        if ($request->status !== MaintenanceRequestStatus::WAITING_FOR_INVOICE_APPROVAL->value) {
            throw new \InvalidArgumentException('Request is not waiting for invoice approval.');
        }

        DB::transaction(function () use ($request, $company) {
            $request->update([
                'invoice_approved_by' => $company->id,
                'invoice_approved_at' => now(),
                'completed_at' => now(),
            ]);
            $this->transitionStatus($request, MaintenanceRequestStatus::CLOSED->value, [], 'company', $company->id);

            $center = $request->approvedCenter;
            if ($center) {
                $center->notify(new \App\Notifications\MaintenanceInvoiceApprovedNotification($request));
            }
            app(\App\Services\DriverNotificationService::class)->notifyMaintenanceRequestClosed($request);
        });
    }

    /**
     * Reject invoice (manager action) - can re-request from center.
     */
    public function rejectInvoice(MaintenanceRequest $request, string $reason): void
    {
        $company = auth('company')->user();
        if ((int) $request->company_id !== (int) $company->id) {
            throw new \InvalidArgumentException('Unauthorized.');
        }

        if ($request->status !== MaintenanceRequestStatus::WAITING_FOR_INVOICE_APPROVAL->value) {
            throw new \InvalidArgumentException('Request is not waiting for invoice approval.');
        }

        $request->update([
            'final_invoice_pdf_path' => null,
            'final_invoice_original_name' => null,
            'final_invoice_amount' => null,
            'final_invoice_uploaded_at' => null,
            'rejection_reason' => $reason,
        ]);
        $this->transitionStatus($request, MaintenanceRequestStatus::IN_PROGRESS->value, [], 'company', $company->id);

        $center = $request->approvedCenter;
        if ($center) {
            $center->notify(new \App\Notifications\MaintenanceInvoiceRejectedNotification($request));
        }
    }

    private function transitionStatus(MaintenanceRequest $request, string $toStatus, array $extra = [], ?string $actorType = null, ?int $actorId = null): void
    {
        $fromStatus = $request->status;

        if (!MaintenanceRequestStatus::canTransition($fromStatus, $toStatus)) {
            throw new \InvalidArgumentException("Invalid status transition from {$fromStatus} to {$toStatus}.");
        }

        $request->update(array_merge(['status' => $toStatus], $extra));
        $request->statusLogs()->create([
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
        ]);
    }

    private function ensureCenterApproved(MaintenanceRequest $request, MaintenanceCenter $center): void
    {
        if ((int) $request->approved_center_id !== (int) $center->id) {
            throw new \InvalidArgumentException('This center is not the approved center for this request.');
        }
    }
}
