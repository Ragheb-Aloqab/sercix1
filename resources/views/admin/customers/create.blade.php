@extends('admin.layouts.app')

@section('title', 'إضافة عميل | SERV.X')
@section('page_title', 'إضافة عميل')

@section('content')
    <div class="max-w-2xl bg-white border border-slate-200 rounded-2xl p-6">
        <form method="POST" action="{{ route('admin.customers.store') }}" class="space-y-4">
            @csrf
            @include('admin.customers.partials.form', ['customer' => null])
        </form>
    </div>
@endsection
