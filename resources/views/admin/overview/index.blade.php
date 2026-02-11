@extends('admin.layouts.app')

@section('title', __('dashboard.overview') . ' | ' . ($siteName ?? 'SERV.X'))
@section('page_title', __('dashboard.page_title_default'))

@section('content')
    <livewire:dashboard.overview />
@endsection
