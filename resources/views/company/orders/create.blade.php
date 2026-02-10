@extends('admin.layouts.app')

@section('title', 'Create Order | SERV.X')
@section('page_title', 'Create Order')

@section('content')
<div class="space-y-6">

    @if (session('success'))
        <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl border border-rose-200 bg-rose-50 text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl border border-rose-200 bg-rose-50 text-rose-800">
            <p class="font-bold mb-2">Errors:</p>
            <ul class="list-disc ms-5 space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-black text-xl">Create Order</p>
                <p class="text-sm text-slate-500 mt-1">Select vehicle, services, branch, and payment method.</p>
            </div>

            <a href="{{ route('company.orders.index') }}"
               class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                Back
            </a>
        </div>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
        <div class="p-5 border-b border-slate-200/70 dark:border-slate-800">
            <h2 class="text-lg font-black">Order Details</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">The order will be created with status: pending.</p>
        </div>

        <div class="p-5">
            <form method="POST" action="{{ route('company.orders.store') }}"
                  class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf

                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Vehicle</label>
                    <select name="vehicle_id" required
                            class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        <option value="">Select Vehicle</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected((string) old('vehicle_id') === (string) $vehicle->id)>
                                {{ $vehicle->name ?? $vehicle->plate_number ?? ('Vehicle #' . $vehicle->id) }}
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_id')
                        <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Services</label>

                    <div class="mt-2 space-y-2">
                        @foreach ($services as $service)
                            @php
                                $old = collect(old('service_ids', []))->map(fn($v) => (string) $v);
                                $checked = $old->contains((string) $service->id);
                                $price = $service->pivot_base_price ?? $service->base_price ?? null;
                                $minutes = $service->pivot_estimated_minutes ?? null;
                            @endphp

                            <label class="flex items-center justify-between gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                                           class="h-5 w-5 rounded border-slate-300"
                                           @checked($checked)>
                                    <div>
                                        <div class="font-bold">{{ $service->name }}</div>
                                        <div class="text-xs text-slate-500">
                                            @if (!is_null($price))
                                                {{ number_format((float) $price, 2) }} SAR
                                            @else
                                                -
                                            @endif
                                            @if (!is_null($minutes))
                                                <span class="mx-2">•</span>{{ (int) $minutes }} min
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    @error('service_ids')
                        <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
                    @enderror
                    @error('service_ids.*')
                        <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Branch</label>
                    <select name="company_branch_id"
                            class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        <option value="">No Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('company_branch_id') === (string) $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_branch_id')
                        <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Notes</label>
                    <textarea name="notes" rows="4"
                              class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                              placeholder="...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Payment Method</label>

                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 cursor-pointer">
                            <input type="radio" name="payment_method" value="cash"
                                   class="h-5 w-5"
                                   @checked(old('payment_method', 'cash') === 'cash')>
                            <span class="font-bold">Cash</span>
                        </label>

                        <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 cursor-pointer">
                            <input type="radio" name="payment_method" value="tap"
                                   class="h-5 w-5"
                                   @checked(old('payment_method') === 'tap')>
                            <span class="font-bold">Online (Tap)</span>
                        </label>

                        <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 cursor-pointer">
                            <input type="radio" name="payment_method" value="bank"
                                   class="h-5 w-5"
                                   @checked(old('payment_method') === 'bank')>
                            <span class="font-bold">Bank Transfer</span>
                        </label>
                    </div>

                    @error('payment_method')
                        <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 flex flex-col sm:flex-row gap-2 pt-2">
                    <button type="submit"
                            class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                        Submit
                    </button>

                    <a href="{{ route('company.orders.index') }}"
                       class="w-full sm:w-auto px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 text-center font-bold">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
