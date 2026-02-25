@extends('admin.layouts.app')

@section('title', __('admin_dashboard.vehicles_overview') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('admin_dashboard.vehicles_overview'))

@section('content')
    <livewire:admin.vehicles-overview />
@endsection
