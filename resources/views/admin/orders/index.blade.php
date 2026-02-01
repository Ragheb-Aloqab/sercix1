@extends('admin.layouts.app')

@section('title', 'الطلبات')

@section('content')
<div class="space-y-4">

  @include('admin.orders.partials._filters')

  <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
    <div class="p-5 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
      <div>
        <p class="text-sm text-slate-500 dark:text-slate-400">أحدث الطلبات</p>
        <h2 class="text-lg font-black">قائمة الطلبات</h2>
      </div>
    </div>

    <div class="p-5 overflow-x-auto">
      @include('admin.orders.partials._table', ['orders' => $orders])
    </div>
  </div>

  <div>
    {{ $orders->links() }}
  </div>
</div>
@endsection
