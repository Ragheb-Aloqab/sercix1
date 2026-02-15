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
            @php $s = $invoiceSettings ?? []; @endphp
            <table cellpadding="0" cellspacing="0" style="width:100%;">
                <tr>
                    @if(!empty($s['logo_path']))
                    <td style="width:56px; vertical-align:top; padding-left:10px;">
                        <img src="{{ $s['logo_path'] }}" alt="" class="logo-img" />
                    </td>
                    @endif
                    <td style="vertical-align:top;">
                        <div class="company-name">{{ $s['company_name'] ?? '' }}</div>
                        @if(!empty($s['address']))<div class="detail-line">{{ $s['address'] }}</div>@endif
                        @if(!empty($s['phone']))<div class="detail-line">{{ __('invoice.phone') }}: {{ $s['phone'] }}</div>@endif
                        @if(!empty($s['tax_number']))<div class="detail-line">{{ __('invoice.vat_number') }}: {{ $s['tax_number'] }}</div>@endif
                        @if(!empty($s['email']))<div class="detail-line">{{ $s['email'] }}</div>@endif
                        @if(!empty($s['website']))<div class="detail-line">{{ $s['website'] }}</div>@endif
                    </td>
                </tr>
            </table>
        </td>
        <td class="header-cell" style="width: 50%;">
            <table cellpadding="0" cellspacing="0" style="width:100%;">
                <tr>
                    <td style="vertical-align:top; padding-left:12px;">
                        <div class="header-label">{{ __('invoice.customer') }}</div>
                        @if($invoice->order && $invoice->order->company)
                        @php $c = $invoice->order->company; @endphp
                        <div class="company-name">{{ $c->company_name ?? '-' }}</div>
                        @if(!empty($c->phone))<div class="detail-line">{{ __('invoice.phone') }}: {{ $c->phone }}</div>@endif
                        @if(!empty($c->email))<div class="detail-line">{{ $c->email }}</div>@endif
                        @if(!empty($c->address))<div class="detail-line">{{ $c->address }}</div>@endif
                        @if(!empty($c->city))<div class="detail-line">{{ $c->city }}</div>@endif
                        @if(!empty($c->contact_person))<div class="detail-line">{{ __('invoice.contact_person') }}: {{ $c->contact_person }}</div>@endif
                        @else
                        <div class="detail-line">—</div>
                        @endif
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
        <td class="meta-cell"><strong>{{ __('invoice.status') }}:</strong> @php $ordStatus = $invoice->order?->status ?? ''; @endphp {{ $ordStatus ? (\Illuminate\Support\Str::startsWith(__('common.status_' . $ordStatus), 'common.') ? $ordStatus : __('common.status_' . $ordStatus)) : '-' }}</td>
    </tr>
    @if($invoice->order && $invoice->order->vehicle)
    <tr>
        <td class="meta-cell"><strong>{{ __('invoice.vehicle') }}:</strong> {{ $invoice->order->vehicle->make ?? '' }} {{ $invoice->order->vehicle->model ?? '-' }}</td>
        <td class="meta-cell"><strong>{{ __('invoice.plate') }}:</strong> {{ $invoice->order->vehicle->plate_number ?? '-' }}</td>
        <td class="meta-cell"></td>
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
        @forelse(($invoice->order && $invoice->order->services) ? $invoice->order->services : [] as $svc)
            @php
                $qty = (float) ($svc->pivot->qty ?? 1);
                $unit = (float) ($svc->pivot->unit_price ?? $svc->pivot->total_price ?? 0);
                $rowTotal = (float) ($svc->pivot->total_price ?? ($qty * $unit));
            @endphp
            <tr>
                <td>{{ $svc->name ?? 'خدمة #' . $svc->id }}</td>
                <td>{{ $qty }}</td>
                <td>{{ number_format($unit, 2) }}</td>
                <td>{{ number_format($rowTotal, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align: center; padding: 15px;">{{ __('invoice.no_services') }}</td></tr>
        @endforelse
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

@if($invoice->order && $invoice->order->notes)
<div style="margin-top: 20px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
    <strong>{{ __('invoice.notes') }}:</strong> {{ $invoice->order->notes }}
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
