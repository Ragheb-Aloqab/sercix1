@extends('admin.layouts.app')

@section('title', __('vehicles.vehicle_mileage_reports') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('vehicles.vehicle_mileage_reports'))
@section('subtitle', __('vehicles.vehicle_mileage_reports_desc'))

@section('content')
@include('company.partials.glass-start', ['title' => __('vehicles.vehicle_mileage_reports')])

<livewire:company.vehicle-mileage-reports />

@include('company.partials.glass-end')
@endsection
