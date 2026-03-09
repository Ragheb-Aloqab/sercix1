@extends('admin.layouts.app')

@section('title', __('reports.comprehensive_report') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('reports.comprehensive_report'))
@section('subtitle', __('reports.comprehensive_report_desc'))

@section('content')
@include('company.partials.glass-start', ['title' => __('reports.comprehensive_report')])

<livewire:company.comprehensive-report />

@include('company.partials.glass-end')
@endsection
