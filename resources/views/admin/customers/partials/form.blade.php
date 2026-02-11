@php
    $c = $customer;
    
@endphp

<div>
    <label class="text-sm font-bold">الاسم</label>
    <input name="name" value="{{ old('name', $c?->name) }}"
           class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
</div>

<div class="grid sm:grid-cols-2 gap-4">
    <div>
        <label class="text-sm font-bold">الجوال</label>
        <input name="phone" value="{{ old('phone', $c?->phone) }}"
               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
    <div>
        <label class="text-sm font-bold">الإيميل</label>
        <input name="email" value="{{ old('email', $c?->email) }}"
               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
</div>

<div class="grid sm:grid-cols-2 gap-4">
    <div>
        <label class="text-sm font-bold">المدينة</label>
        <input name="city" value="{{ old('city', $c?->city) }}"
               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
    <div class="flex items-end">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="status" value="1" {{ old('status', $c?->status) ? 'checked' : '' }}>
            <span>نشط</span>
        </label>
    </div>
</div>

<div>
    <label class="text-sm font-bold">العنوان</label>
    <textarea name="address" rows="3"
              class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">{{ old('address', $c?->address) }}</textarea>
</div>

<div>
    <label class="text-sm font-bold">ملاحظات</label>
    <textarea name="notes" rows="3"
              class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">{{ old('notes', $c?->notes) }}</textarea>
</div>

<div class="flex gap-2">
    <button class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold">{{ __('common.save') }}</button>
    <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 font-bold">رجوع</a>
</div>
@extends('admin.layouts.app')

@section('title', 'تعديل عميل | SERV.X')
@section('page_title', 'تعديل بيانات العميل')

@section('content')
    <div class="max-w-2xl bg-white border border-slate-200 rounded-2xl p-6">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="space-y-4">
          
            @csrf
            @method('PUT')
           {{-- @include('admin.customers.partials.form', ['customer' => $customer])--}}
           {{--Start Partisal Form--}}
           <div>
              <label class="text-sm font-bold">الاسم</label>
              <input name="company_name" value="{{ old('company_name', $customer?->company_name) }}"
                     class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
              <div>
                  <label class="text-sm font-bold">الجوال</label>
                  <input name="phone" value="{{ old('phone', $customer?->phone) }}"
                         class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
              </div>
              <div>
                  <label class="text-sm font-bold">الإيميل</label>
                  <input name="email" value="{{ old('email', $customer?->email) }}"
                         class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
              </div>
          </div>
          
          <div class="grid sm:grid-cols-2 gap-4">
              <div>
                  <label class="text-sm font-bold">المدينة</label>
                  <input name="city" value="{{ old('city', $customer?->city) }}"
                         class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
              </div>
              <div class="flex items-end">
                  <label class="flex items-center gap-2 text-sm">
                      <input type="checkbox" name="status" value="1" {{ old('status', $customer?->status) ==="active"? 'checked' : '' }}>
                      <span>نشط</span>
                  </label>
              </div>
          </div>
          
          <div>
              <label class="text-sm font-bold">العنوان</label>
              <textarea name="address" rows="3"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">{{ old('address', $customer?->address) }}</textarea>
          </div>
          
          <div>
              <label class="text-sm font-bold">ملاحظات</label>
              <textarea name="notes" rows="3"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">{{ old('notes', $customer?->notes) }}</textarea>
          </div>
          
          <div class="flex gap-2">
              <button class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold">{{ __('common.save') }}</button>
              <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 font-bold">رجوع</a>
          </div>
           {{--End Partisal Form--}}
        </form>
    </div>
@endsection
