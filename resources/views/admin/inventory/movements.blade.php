@extends('admin.layouts.app')

@section('title', 'سجل حركة المخزون | SERV.X')
@section('page_title', 'سجل حركة المخزون')
@section('subtitle', 'متابعة عمليات الإدخال والإخراج والتعديلات')

@section('content')
    <livewire:admin.inventory-movements />
@endsection
