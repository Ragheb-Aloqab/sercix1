@extends('admin.layouts.app')

@section('title', $center->name . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', $center->name)

@section('content')
    <div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
        <div class="dashboard-content max-w-6xl mx-auto space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="dash-page-title">{{ $center->name }}</h1>
                    <div class="dash-title-accent"></div>
                    <p class="text-sm text-slate-400 mt-1">
                        @if ($center->status === 'active')
                            <span class="text-emerald-500">{{ __('maintenance.active') ?? 'Active' }}</span>
                        @else
                            <span class="text-amber-500">{{ __('maintenance.suspended') ?? 'Suspended' }}</span>
                        @endif
                        · {{ __('maintenance.registered') ?? 'Registered' }} {{ $center->created_at?->format('Y-m-d') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.maintenance-centers.edit', $center) }}" class="dash-btn dash-btn-primary">
                        <i class="fa-solid fa-pen-to-square me-2"></i>{{ __('common.edit') ?? 'Edit' }}
                    </a>
                    <form method="POST" action="{{ route('admin.maintenance-centers.toggle-status', $center) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="dash-btn {{ $center->status === 'active' ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}"
                            onclick="return confirm('{{ $center->status === 'active' ? (__('maintenance.confirm_suspend') ?? 'Suspend?') : (__('maintenance.confirm_activate') ?? 'Activate?') }}');">
                            <i class="fa-solid {{ $center->status === 'active' ? 'fa-pause' : 'fa-play' }} me-2"></i>
                            {{ $center->status === 'active' ? (__('maintenance.suspend') ?? 'Suspend') : (__('maintenance.activate') ?? 'Activate') }}
                        </button>
                    </form>
                    <a href="{{ route('admin.maintenance-centers.index') }}" class="dash-btn dash-btn-secondary">
                        <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="dash-card border-emerald-500/30 bg-emerald-500/10">{{ session('success') }}</div>
            @endif

            {{-- Profile & Contact --}}
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('maintenance.center_profile') ?? 'Center Profile' }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><span class="text-slate-500">{{ __('maintenance.phone') }}:</span> {{ $center->phone }}</div>
                    <div><span class="text-slate-500">{{ __('maintenance.email') }}:</span> {{ $center->email ?? '-' }}</div>
                    <div><span class="text-slate-500">{{ __('maintenance.city') }}:</span> {{ $center->city ?? '-' }}</div>
                    <div><span class="text-slate-500">{{ __('maintenance.address') }}:</span> {{ $center->address ?? '-' }}</div>
                    @if (!empty($center->service_categories) && is_array($center->service_categories))
                        <div class="md:col-span-2">
                            <span class="text-slate-500">{{ __('maintenance.service_categories') ?? 'Service Categories' }}:</span>
                            {{ implode(', ', $center->service_categories) }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Financial Summary --}}
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('maintenance.financial_summary') ?? 'Financial Summary' }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50">
                        <p class="text-xs text-slate-500">{{ __('maintenance.approved_quotations') ?? 'Approved Quotations' }}</p>
                        <p class="text-xl font-bold">{{ $approvedQuotationsCount ?? 0 }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50">
                        <p class="text-xs text-slate-500">{{ __('maintenance.completed_jobs') ?? 'Completed Jobs' }}</p>
                        <p class="text-xl font-bold">{{ $completedJobs ?? 0 }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50">
                        <p class="text-xs text-slate-500">{{ __('maintenance.total_invoiced') ?? 'Total Invoiced' }}</p>
                        <p class="text-xl font-bold">{{ number_format((float) ($totalInvoiced ?? 0), 2) }} {{ __('company.sar') ?? 'SAR' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50">
                        <p class="text-xs text-slate-500">{{ __('maintenance.total_paid') ?? 'Total Paid' }}</p>
                        <p class="text-xl font-bold">{{ number_format((float) ($totalPaid ?? 0), 2) }} {{ __('company.sar') ?? 'SAR' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20">
                        <p class="text-xs text-slate-500">{{ __('maintenance.outstanding_balance') ?? 'Outstanding' }}</p>
                        <p class="text-xl font-bold">{{ number_format((float) ($outstanding ?? 0), 2) }} {{ __('company.sar') ?? 'SAR' }}</p>
                    </div>
                </div>
            </div>

            {{-- Activity: Assigned Requests --}}
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('maintenance.assigned_requests') ?? 'Assigned Maintenance Requests' }}</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-950">
                            <tr class="text-slate-600 dark:text-slate-300">
                                <th class="text-start px-4 py-3">#</th>
                                <th class="text-start px-4 py-3">{{ __('driver.vehicle') ?? 'Vehicle' }}</th>
                                <th class="text-start px-4 py-3">{{ __('maintenance.status') ?? 'Status' }}</th>
                                <th class="text-start px-4 py-3">{{ __('common.date') ?? 'Date' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($assignedRequests ?? [] as $req)
                                <tr>
                                    <td class="px-4 py-3">{{ $req->id }}</td>
                                    <td class="px-4 py-3">{{ $req->vehicle?->plate_number ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $req->status ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $req->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">{{ __('maintenance.no_requests') ?? 'No assigned requests.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Quotations Submitted --}}
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('maintenance.quotations_submitted') ?? 'Quotations Submitted' }}</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-950">
                            <tr class="text-slate-600 dark:text-slate-300">
                                <th class="text-start px-4 py-3">Request #</th>
                                <th class="text-start px-4 py-3">{{ __('maintenance.price') ?? 'Price' }}</th>
                                <th class="text-start px-4 py-3">{{ __('maintenance.approved') ?? 'Approved' }}</th>
                                <th class="text-start px-4 py-3">{{ __('common.date') ?? 'Date' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($quotations ?? [] as $q)
                                <tr>
                                    <td class="px-4 py-3">{{ $q->maintenance_request_id ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold">{{ number_format((float) ($q->price ?? 0), 2) }} {{ __('company.sar') ?? 'SAR' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($q->maintenanceRequest && (int) $q->maintenanceRequest->approved_quotation_id === (int) $q->id)
                                            <span class="text-emerald-600">{{ __('common.yes') ?? 'Yes' }}</span>
                                        @else
                                            <span class="text-slate-500">{{ __('common.no') ?? 'No' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $q->submitted_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">{{ __('maintenance.no_quotations') ?? 'No quotations submitted.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
