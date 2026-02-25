@extends('admin.layouts.app')

@section('title', 'تفاصيل الطلب #' . $order->id)
@section('page_title', 'تفاصيل الطلب #' . $order->id)
@section('subtitle', 'الطلب والخدمات')

@section('content')
    <div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
        <livewire:admin.order-show :order="$order" />
    </div>
@endsection
