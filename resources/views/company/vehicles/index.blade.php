@extends('admin.layouts.app')

@section('title', __('vehicles.title') . ' | Servx Motors')
@section('page_title', __('vehicles.page_title'))
@section('subtitle', __('vehicles.manage_vehicles'))

@section('content')
<x-company.glass :title="__('vehicles.vehicles_list')">
    <livewire:company.vehicles-list />
</x-company.glass>
@endsection
