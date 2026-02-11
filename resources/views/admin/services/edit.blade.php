@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6">
  <h1 class="text-2xl font-black">{{ __('common.edit_service') }}</h1>

  <form method="POST" action="{{ route('admin.services.update', $service) }}" class="mt-6 bg-white border rounded-2xl p-6">
    @method('PUT')
    @include('admin.services.partials._form', ['service' => $service])
  </form>
</div>
@endsection
