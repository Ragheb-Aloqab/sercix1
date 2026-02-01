@extends('admin.layouts.app')

@section('title', 'إضافة عنصر | SERV.X')
@section('page_title', 'إضافة عنصر للمخزون')

@section('content')
    <div class="max-w-2xl bg-white border border-slate-200 rounded-2xl p-6">
        <form method="POST" action="{{ route('admin.inventory.store') }}" class="space-y-4">
            @csrf
            @include('admin.inventory.partials.form', ['item' => null])
        </form>
    </div>
@endsection
