@extends('admin.layouts.app')

@section('title', __('reports.tax_reports') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('reports.tax_reports'))
@section('subtitle', __('reports.tax_reports_desc'))

@section('content')
@include('company.partials.glass-start', ['title' => __('reports.tax_reports')])

<livewire:company.tax-report />

@include('company.partials.glass-end')
@endsection
