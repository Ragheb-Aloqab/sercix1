@extends('admin.layouts.app')

@section('title', __('maintenance.maintenance_centers') . ' | Servx Motors')
@section('page_title', __('maintenance.maintenance_centers'))
@section('subtitle', __('maintenance.maintenance_centers_desc') ?? 'Active centers available for RFQ')

@section('content')
@include('company.partials.glass-start', ['title' => __('maintenance.maintenance_centers')])
<div class="dash-card">
    <p class="text-servx-silver mb-4">{{ __('maintenance.centers_read_only_desc') ?? 'Maintenance centers are managed by Super Admin. When sending an RFQ, you can select specific centers or broadcast to all active centers.' }}</p>

    <h3 class="dash-section-title">{{ __('maintenance.active_centers') ?? 'Active Centers' }}</h3>
    @if($centers->isEmpty())
        <p class="text-servx-silver">{{ __('maintenance.no_active_centers') ?? 'No active maintenance centers available. Contact admin.' }}</p>
    @else
        <div class="overflow-x-auto mt-4">
            <table class="w-full">
                <thead>
                    <tr class="text-start text-servx-silver text-sm border-b border-slate-600/50">
                        <th class="pb-3 pe-4">{{ __('maintenance.center_name') }}</th>
                        <th class="pb-3 pe-4">{{ __('maintenance.phone') }}</th>
                        <th class="pb-3 pe-4">{{ __('maintenance.email') }}</th>
                        <th class="pb-3 pe-4">{{ __('maintenance.city') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($centers as $c)
                        <tr class="border-b border-slate-600/30">
                            <td class="py-4 pe-4">{{ $c->name }}</td>
                            <td class="py-4 pe-4">{{ $c->phone }}</td>
                            <td class="py-4 pe-4">{{ $c->email ?? '-' }}</td>
                            <td class="py-4 pe-4">{{ $c->city ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@include('company.partials.glass-end')
@endsection
