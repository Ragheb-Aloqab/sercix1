@extends('admin.layouts.app')

@section('title', 'تفاصيل الطلب #' . $order->id)
@section('page_title', 'تفاصيل الطلب #' . $order->id)
@section('subtitle', 'الطلب والخدمات')

@section('content')
    <livewire:admin.order-show :order="$order" :technicians="$technicians" />
@endsection
