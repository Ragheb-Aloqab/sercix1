{{--
    PLACEHOLDER: Fuel Balance Page - Coming Soon
    Route: /dashboard/companies/fuel-balance
    Future: Display fuel balance for each vehicle.
    This view is for preview/layout purposes only.
--}}
@extends('admin.layouts.app')

@section('title', __('company.fuel_balance_page') . ' | ' . ($siteName ?? 'SERV.X'))
@section('page_title', __('company.fuel_balance_page'))
@section('subtitle', __('company.fuel_balance_placeholder_desc'))

@section('content')
    <div class="space-y-6">
        {{-- Placeholder card - ready for future dynamic content --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-6 sm:p-8">
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100">
                {{ __('company.fuel_balance_coming_soon') }}
            </h1>
            <p class="mt-2 text-slate-500 dark:text-slate-400">
                {{ __('company.fuel_balance_placeholder_desc') }}
            </p>
        </div>
    </div>
@endsection
