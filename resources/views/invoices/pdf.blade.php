<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>فاتورة {{ $invoice->invoice_number ?? $invoice->id }}</title>
    <style>
        body { font-family: xbriyaz, sans-serif; font-size: 12px; color: #333; padding: 20px; direction: rtl; }
        table { width: 100%; border-collapse: collapse; }
        .header-table { margin-bottom: 20px; }
        .header-cell { padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; vertical-align: top; width: 50%; }
        .header-label { font-size: 11px; font-weight: bold; color: #64748b; margin-bottom: 8px; }
        .company-name { font-size: 15px; font-weight: bold; color: #0f172a; margin-bottom: 6px; }
        .detail-line { font-size: 11px; color: #475569; line-height: 1.5; margin: 2px 0; }
        .meta-table { width: 100%; margin-bottom: 15px; background: #f1f5f9; border: 1px solid #e2e8f0; }
        .meta-cell { padding: 8px 12px; font-size: 11px; }
        .meta-cell strong { color: #64748b; }
        .barcode-num { font-size: 10px; font-weight: bold; margin-top: 4px; }
        .logo-img { width: 56px; height: 56px; border: 1px solid #e2e8f0; }
        th, td { padding: 8px 10px; border: 1px solid #e2e8f0; text-align: right; }
        th { background: #f1f5f9; font-weight: bold; }
        .total-row { font-weight: bold; background: #f8fafc; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 10px; color: #64748b; }
        .invoice-img { max-width: 100%; max-height: 200px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>

{{-- Top accent bar --}}
<div style="height: 4px; background: #0f172a; margin-bottom: 18px;"></div>

{{-- Row 1: Company Info | Customer Info + Barcode --}}
<table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="header-cell" style="width: 50%;">
            <div class="header-label">{{ __('invoice.issuer') }}</div>
            <table cellpadding="0" cellspacing="0" style="width:100%;">
                <tr>
                    @if(!empty($invoiceSettings['logo_path']))
                    <td style="width:56px; vertical-align:top; padding-left:10px;">
                        <img src="{{ $invoiceSettings['logo_path'] }}" alt="" class="logo-img" />
                    </td>
                    @endif
                    <td style="vertical-align:top;">
                        <div class="company-name">{{ $invoiceSettings['company_name'] ?? '' }}</div>
                        @if(!empty($invoiceSettings['address']))<div class="detail-line">{{ $invoiceSettings['address'] }}</div>@endif
                        @if(!empty($invoiceSettings['phone']))<div class="detail-line">{{ __('invoice.phone') }}: {{ $invoiceSettings['phone'] }}</div>@endif
                        @if(!empty($invoiceSettings['tax_number']))<div class="detail-line">{{ __('invoice.vat_number') }}: {{ $invoiceSettings['tax_number'] }}</div>@endif
                        @if(!empty($invoiceSettings['email']))<div class="detail-line">{{ $invoiceSettings['email'] }}</div>@endif
                        @if(!empty($invoiceSettings['website']))<div class="detail-line">{{ $invoiceSettings['website'] }}</div>@endif
                    </td>
                </tr>
            </table>
        </td>
        <td class="header-cell" style="width: 50%;">
            <table cellpadding="0" cellspacing="0" style="width:100%;">
                <tr>
                    <td style="vertical-align:top; padding-left:12px;">
                        <div class="header-label">{{ __('invoice.customer') }}</div>
                        @if($company ?? null)
                        <div class="company-name">{{ $company->company_name ?? '-' }}</div>
                        @if(!empty($company->phone))<div class="detail-line">{{ __('invoice.phone') }}: {{ $company->phone }}</div>@endif
                        @if(!empty($company->email))<div class="detail-line">{{ $company->email }}</div>@endif
                        @if(!empty($company->address))<div class="detail-line">{{ $company->address }}</div>@endif
                        @if(!empty($company->city))<div class="detail-line">{{ $company->city }}</div>@endif
                        @if(!empty($company->contact_person))<div class="detail-line">{{ __('invoice.contact_person') }}: {{ $company->contact_person }}</div>@endif
                        @else
                        <div class="detail-line">—</div>
                        @endif
                        <div class="header-label" style="margin-top:12px;">{{ __('invoice.driver_name') }}</div>
                        <div class="detail-line">{{ $invoice->driver_name ?? '-' }}</div>
                        <div class="detail-line">{{ __('invoice.driver_phone') }}: {{ $invoice->driver_phone ?? '-' }}</div>
                    </td>
                    @if($barcodeHtml ?? null)
                    <td style="width:130px; vertical-align:middle; text-align:center;">
                        <div style="padding:8px; border:1px solid #e2e8f0;">
                            {!! $barcodeHtml !!}
                            <div class="barcode-num">{{ $barcodeData }}</div>
                        </div>
                    </td>
                    @endif
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Row 2: Invoice Meta --}}
<table class="meta-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="meta-cell"><strong>{{ __('invoice.invoice_number_label') }}:</strong> {{ $invoice->invoice_number ?? 'INV-' . $invoice->id }}</td>
        <td class="meta-cell"><strong>{{ __('invoice.date_label') }}:</strong> {{ $invoice->created_at?->format('d-m-Y') ?? '-' }}</td>
        <td class="meta-cell"><strong>{{ __('invoice.status') }}:</strong> {{ $ordStatusLabel ?? '-' }}</td>
    </tr>
    <tr>
        <td class="meta-cell"><strong>{{ __('invoice.service') }}:</strong> {{ $invoice->service_type_label }}</td>
        <td class="meta-cell"><strong>{{ __('invoice.vehicle') }}:</strong> {{ $invoice->vehicle ? trim(($invoice->vehicle->make ?? '') . ' ' . ($invoice->vehicle->model ?? '')) : '-' }}</td>
        <td class="meta-cell"><strong>{{ __('invoice.plate') }}:</strong> {{ $invoice->vehicle?->plate_number ?? '-' }}</td>
    </tr>
    @if($invoice->vehicle)
    <tr>
        <td class="meta-cell"><strong>{{ __('invoice.vehicle_type') }}:</strong> {{ $invoice->vehicle->type ?? '-' }}</td>
        <td class="meta-cell"><strong>{{ __('invoice.vehicle_year') }}:</strong> {{ $invoice->vehicle->year ?? '-' }}</td>
        <td class="meta-cell"><strong>{{ __('invoice.vehicle_color') }}:</strong> {{ $invoice->vehicle->color ?? '-' }}</td>
    </tr>
    @endif
</table>

{{-- Services table --}}
<table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th style="width: 45%;">{{ __('invoice.service') }}</th>
            <th style="width: 15%;">{{ __('invoice.quantity') }}</th>
            <th style="width: 20%;">{{ __('invoice.unit_price_sar') }}</th>
            <th style="width: 20%;">{{ __('invoice.total_sar') }}</th>
        </tr>
    </thead>
    <tbody>
        @if($invoice->isFuel() && $invoice->fuelRefill)
            <tr>
                <td>{{ $invoice->service_type_label }} — {{ number_format($invoice->fuelRefill->liters, 1) }} {{ __('fuel.quantity') }}</td>
                <td>1</td>
                <td>{{ number_format($invoice->fuelRefill->cost, 2) }}</td>
                <td>{{ number_format($invoice->fuelRefill->cost, 2) }}</td>
            </tr>
        @else
            @forelse($orderItems as $os)
                <tr>
                    <td>{{ $os->name }}</td>
                    <td>{{ $os->qty }}</td>
                    <td>{{ number_format($os->unit, 2) }}</td>
                    <td>{{ number_format($os->rowTotal, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center; padding: 15px;">{{ __('invoice.no_services') }}</td></tr>
            @endforelse
        @endif
    </tbody>
</table>

{{-- Totals --}}
<table style="margin-top: 20px; max-width: 350px; margin-right: 0; margin-left: auto;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="padding: 6px 10px; border: 1px solid #e2e8f0;"><strong>{{ __('invoice.subtotal') }}</strong></td>
        <td style="padding: 6px 10px; border: 1px solid #e2e8f0;">{{ number_format($invoice->subtotal ?? $total ?? 0, 2) }} {{ __('company.sar') }}</td>
    </tr>
    <tr>
        <td style="padding: 6px 10px; border: 1px solid #e2e8f0;"><strong>{{ __('invoice.tax') }}</strong></td>
        <td style="padding: 6px 10px; border: 1px solid #e2e8f0;">{{ number_format($invoice->tax ?? 0, 2) }} {{ __('company.sar') }}</td>
    </tr>
    <tr class="total-row">
        <td style="padding: 8px 10px; border: 1px solid #e2e8f0; background: #f8fafc;"><strong>{{ __('invoice.total') }}</strong></td>
        <td style="padding: 8px 10px; border: 1px solid #e2e8f0; background: #f8fafc;">{{ number_format($total ?? 0, 2) }} {{ __('company.sar') }}</td>
    </tr>
    <tr>
        <td style="padding: 6px 10px; border: 1px solid #e2e8f0;"><strong>{{ __('invoice.paid') }}</strong></td>
        <td style="padding: 6px 10px; border: 1px solid #e2e8f0; color: #059669;">{{ number_format($paidAmount ?? 0, 2) }} {{ __('company.sar') }}</td>
    </tr>
    <tr class="total-row">
        <td style="padding: 8px 10px; border: 1px solid #e2e8f0; background: #f8fafc;"><strong>{{ __('invoice.remaining') }}</strong></td>
        <td style="padding: 8px 10px; border: 1px solid #e2e8f0; background: #f8fafc; color: #dc2626;">{{ number_format($remainingAmount ?? 0, 2) }} {{ __('company.sar') }}</td>
    </tr>
</table>

@if($invoiceImagePath ?? null)
<div style="margin-top: 20px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
    <strong>{{ __('invoice.uploaded_invoice') }}:</strong><br>
    <img src="{{ $invoiceImagePath }}" alt="Invoice" class="invoice-img" style="margin-top:8px;" />
</div>
@endif

@if($invoice->order && $invoice->order->notes)
<div style="margin-top: 20px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
    <strong>{{ __('invoice.notes') }}:</strong> {{ $invoice->order->notes }}
</div>
@endif

@if($invoice->fuelRefill && $invoice->fuelRefill->notes)
<div style="margin-top: 20px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
    <strong>{{ __('invoice.notes') }}:</strong> {{ $invoice->fuelRefill->notes }}
</div>
@endif

<div class="footer" style="margin-top: 40px;">
    @if(!empty($invoiceSettings['notes'] ?? ''))
        <p>{{ $invoiceSettings['notes'] }}</p>
    @endif
    <p>{{ __('invoice.invoice_auto_created') }} — {{ now()->format('Y-m-d H:i') }}</p>
</div>

</body>
</html>
