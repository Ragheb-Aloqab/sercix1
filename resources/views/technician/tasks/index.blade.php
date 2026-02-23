@extends('admin.layouts.app')

@section('title', 'مهامي | Servx Motors')
@section('page_title', 'مهامي')
@section('subtitle', 'قائمة المهام المسندة')

@section('content')
    <livewire:tech.tasks-list />
@endsection
