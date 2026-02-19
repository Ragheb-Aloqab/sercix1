@extends('admin.layouts.app')

@section('title', 'تفاصيل الطلب | SERV.X')
@section('page_title', 'تفاصيل الطلب')
@section('subtitle', 'الطلب والفاتورة والمدفوعات')

@section('content')
    <livewire:company.order-show :order="$order" :key="'order-'.$order->id" />
@endsection
