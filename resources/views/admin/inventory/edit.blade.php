@extends('admin.layouts.app')

@section('title', 'تعديل عنصر | SERV.X')
@section('page_title', 'تعديل عنصر المخزون')

@section('content')
    <div class="max-w-2xl bg-white border border-slate-200 rounded-2xl p-6">
        <form method="POST" action="{{ route('admin.inventory.update', $item) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.inventory.partials.form', ['item' => $item])
        </form>
    </div>
@endsection
