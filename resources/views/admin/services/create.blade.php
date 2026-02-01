@extends('admin.layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto p-6">
        <h1 class="text-2xl font-black">إضافة خدمة</h1>

        <form method="POST" action="{{ route('admin.services.store') }}" class="mt-6 bg-white border rounded-2xl p-6">
            @include('admin.services.partials._form')
        </form>
    </div>
@endsection
