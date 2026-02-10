@extends('admin.layouts.app')

@section('title', 'طلباتي | SERV.X')
@section('page_title', 'طلباتي')
@section('subtitle', 'قائمة الطلبات')

@section('content')
    <livewire:company.orders-list />
@endsection
