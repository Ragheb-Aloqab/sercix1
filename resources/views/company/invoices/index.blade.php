@extends('admin.layouts.app')

@section('page_title', __('invoice.invoices_page_title'))
@section('subtitle', __('invoice.invoices_subtitle'))

@section('content')
<x-company.glass :title="__('invoice.invoices_page_title')">
    <livewire:company.invoices-list />
</x-company.glass>
@endsection
