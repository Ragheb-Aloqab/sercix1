<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ __('fuel.register_refill') }} — {{ $siteName ?? 'SERV.X' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: "Tajawal", system-ui, sans-serif; } .shadow-soft { box-shadow: 0 18px 60px rgba(0,0,0,.12); } </style>
</head>
<body class="bg-slate-50 text-slate-900">
<div class="min-h-screen">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-10">
        <div class="max-w-2xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('driver.dashboard') }}" class="font-extrabold text-lg"><i class="fa-solid fa-arrow-right me-2"></i>{{ $siteName ?? 'SERV.X' }}</a>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-black mb-6">{{ __('fuel.register_refill') }}</h1>
        <p class="text-slate-600 mb-6">{{ __('fuel.register_refill_desc') }}</p>

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl border border-rose-200 bg-rose-50 text-rose-800">
                <ul class="list-disc ms-5 space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('driver.fuel-refill.store') }}" enctype="multipart/form-data" class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-bold text-slate-700">{{ __('fuel.vehicle') }} *</label>
                <select name="vehicle_id" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-amber-100">
                    <option value="">— {{ __('fuel.vehicle') }} —</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected(old('vehicle_id') == $v->id)>{{ $v->plate_number }} — {{ $v->make ?? '' }} {{ $v->model ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-700">{{ __('fuel.liters') }} *</label>
                    <input type="number" name="liters" step="0.01" min="0.01" value="{{ old('liters') }}" placeholder="{{ __('driver.example_liters') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-amber-100" />
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">{{ __('fuel.cost') }} *</label>
                    <input type="number" name="cost" step="0.01" min="0" value="{{ old('cost') }}" placeholder="{{ __('driver.example_cost') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-amber-100" />
                </div>
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">{{ __('fuel.refilled_at') }} *</label>
                <input type="datetime-local" name="refilled_at" value="{{ old('refilled_at', now()->format('Y-m-d\TH:i')) }}" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-amber-100" />
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">{{ __('fuel.odometer_km') }} — {{ __('common.optional') }}</label>
                <input type="number" name="odometer_km" min="0" value="{{ old('odometer_km') }}" placeholder="{{ __('fuel.odometer_hint') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-amber-100" />
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">{{ __('fuel.fuel_type') }}</label>
                <select name="fuel_type" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-amber-100">
                    <option value="petrol" @selected(old('fuel_type', 'petrol') === 'petrol')>{{ __('fuel.petrol') }}</option>
                    <option value="diesel" @selected(old('fuel_type') === 'diesel')>{{ __('fuel.diesel') }}</option>
                    <option value="premium" @selected(old('fuel_type') === 'premium')>{{ __('fuel.premium') }}</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">{{ __('fuel.notes') }} ({{ __('common.optional') }})</label>
                <textarea name="notes" rows="2" placeholder="{{ __('driver.notes_placeholder') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-amber-100">{{ old('notes') }}</textarea>
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">{{ __('fuel.receipt_image') }} ({{ __('common.optional') }})</label>
                <p class="text-xs text-slate-500 mt-1 mb-2">{{ __('fuel.receipt_hint') }}</p>
                <input type="file" name="receipt" accept="image/*" capture="environment" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:ring-4 focus:ring-amber-100 file:me-2 file:rounded-xl file:border-0 file:bg-amber-100 file:px-4 file:py-2 file:font-bold file:text-amber-800" />
                @error('receipt')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-extrabold py-3">
                    <i class="fa-solid fa-gas-pump me-2"></i>{{ __('fuel.submit_refill') }}
                </button>
                <a href="{{ route('driver.dashboard') }}" class="px-6 py-3 rounded-2xl border border-slate-200 font-bold">{{ __('common.cancel') }}</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>
