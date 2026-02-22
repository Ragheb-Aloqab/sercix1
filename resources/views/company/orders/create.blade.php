@extends('admin.layouts.app')

@section('title', 'إنشاء طلب | SERV.X')
@section('page_title', __('orders.new_service_request'))
@section('subtitle', 'طلب خدمة جديدة')

@section('content')
@include('company.partials.glass-start', ['title' => __('orders.new_service_request')])
    <livewire:company.order-create />
@include('company.partials.glass-end')
@endsection
