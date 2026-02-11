@extends('admin.layouts.app')

@section('title', 'مراجعة التحويلات البنكية | SERV.X')
@section('page_title', 'مراجعة التحويلات البنكية')
@section('subtitle', 'عرض إيصالات التحويل وتأكيد الاستلام')

@section('content')
    <livewire:admin.bank-transfer-review />
@endsection
