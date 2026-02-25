@extends('admin.layouts.app')

@section('title', __('admin_dashboard.companies_overview') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('admin_dashboard.companies_overview'))

@section('content')
    <livewire:admin.companies-list />
@endsection
