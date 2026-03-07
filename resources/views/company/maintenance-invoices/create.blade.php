@extends('admin.layouts.app')

@section('title', __('maintenance.upload_maintenance_invoice'))
@section('page_title', __('maintenance.upload_maintenance_invoice'))
@section('subtitle', __('maintenance.add_invoice_desc'))

@section('content')
@include('company.partials.glass-start', ['title' => __('maintenance.upload_maintenance_invoice')])
    <livewire:company.create-maintenance-invoice />
@include('company.partials.glass-end')
@endsection
