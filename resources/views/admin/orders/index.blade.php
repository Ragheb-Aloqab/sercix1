@extends('admin.layouts.app')

@section('title', 'الطلبات')
@section('page_title', 'الطلبات')
@section('subtitle', 'قائمة الطلبات والفلاتر')

@section('content')
    <livewire:admin.orders-list />
@endsection
