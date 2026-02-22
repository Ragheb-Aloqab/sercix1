@extends('admin.layouts.app')

@section('title', 'تفاصيل الطلب | SERV.X')
@section('page_title', __('orders.order_details'))
@section('subtitle', __('orders.order') . ' #' . $order->id)

@section('content')
@include('company.partials.glass-start', ['title' => __('orders.order') . ' #' . $order->id])
    <livewire:company.order-show :order="$order" :key="'order-'.$order->id" />
@include('company.partials.glass-end')
@endsection
