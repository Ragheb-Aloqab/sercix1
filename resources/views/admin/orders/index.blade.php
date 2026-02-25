@extends('admin.layouts.app')

@section('title', __('dashboard.orders') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('dashboard.orders'))
@section('subtitle', __('livewire.orders_list') ?? 'Orders list')

@section('content')
    <div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
        <livewire:admin.orders-list />
    </div>
@endsection
