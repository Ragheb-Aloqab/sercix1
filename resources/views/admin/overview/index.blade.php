@extends('admin.layouts.app')

@section('title', __('admin_dashboard.super_admin_dashboard') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('admin_dashboard.super_admin_dashboard'))

@section('content')
    <livewire:admin.super-dashboard />
@endsection
