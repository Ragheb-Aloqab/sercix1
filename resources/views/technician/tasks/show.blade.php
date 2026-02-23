@extends('admin.layouts.app')

@section('title', 'تفاصيل المهمة | Servx Motors')
@section('page_title', 'تفاصيل المهمة')
@section('subtitle', 'صور قبل/بعد وتأكيد الإنجاز')

@section('content')
    <livewire:tech.task-show :order="$order" />
@endsection
