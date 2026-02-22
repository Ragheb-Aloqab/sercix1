@extends('admin.layouts.app')

@section('title', 'طلباتي | SERV.X')
@section('page_title', __('dashboard.orders'))
@section('subtitle', __('orders.orders_list_desc') ?? 'قائمة الطلبات')

@section('content')
@include('company.partials.glass-start', ['title' => __('dashboard.orders')])
    <livewire:company.orders-list />
@include('company.partials.glass-end')
@endsection
