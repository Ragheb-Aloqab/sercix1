@extends('admin.layouts.app')

@section('title', __('admin_dashboard.quick_add_company') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('admin_dashboard.quick_add_company'))

@section('content')
    <div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
        <div class="dashboard-content max-w-2xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-center sm:text-start">
                    <h1 class="dash-page-title">{{ __('admin_dashboard.quick_add_company') }}</h1>
                    <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
                </div>
                <a href="{{ route('admin.customers.index') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
                </a>
            </div>
            <div class="dash-card">
                <form method="POST" action="{{ route('admin.customers.store') }}" class="space-y-4" enctype="multipart/form-data">
                    @csrf
                    @include('admin.customers.partials.form', ['customer' => null])
                </form>
            </div>
        </div>
    </div>
@endsection
