@php $c = $customer ?? null; @endphp

<div>
    <label class="text-xs font-semibold text-slate-400">{{ __('common.company') }}</label>
    <input name="company_name" value="{{ old('company_name', $c?->company_name) }}" required
           class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
</div>

<div class="grid sm:grid-cols-2 gap-4">
    <div>
        <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.phone') }}</label>
        <input name="phone" value="{{ old('phone', $c?->phone) }}" {{ $c ? '' : 'required' }}
               class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
    </div>
    <div>
        <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.email') }}</label>
        <input name="email" type="email" value="{{ old('email', $c?->email) }}"
               class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
    </div>
</div>

@if(!$c)
<div class="grid sm:grid-cols-2 gap-4">
    <div>
        <label class="text-xs font-semibold text-slate-400">{{ __('auth.password') ?? 'Password' }}</label>
        <input name="password" type="password" placeholder="{{ __('auth.optional_leave_blank') ?? 'Optional - auto-generated if blank' }}"
               class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
    </div>
    <div>
        <label class="text-xs font-semibold text-slate-400">{{ __('auth.confirm_password') ?? 'Confirm Password' }}</label>
        <input name="password_confirmation" type="password"
               class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
    </div>
</div>
@endif

<div class="grid sm:grid-cols-2 gap-4">
    <div>
        <label class="text-xs font-semibold text-slate-400">{{ __('common.city') ?? 'City' }}</label>
        <input name="city" value="{{ old('city', $c?->city) }}"
               class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
    </div>
    @if($c !== null)
    <div class="flex items-end">
        <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
            <input type="checkbox" name="status" value="1" {{ old('status', $c?->status) === 'active' ? 'checked' : '' }}
                   class="rounded accent-sky-500">
            {{ __('admin_dashboard.active') }}
        </label>
    </div>
    @endif
</div>

<div>
    <label class="text-xs font-semibold text-slate-400">{{ __('common.address') ?? 'Address' }}</label>
    <textarea name="address" rows="3"
              class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">{{ old('address', $c?->address) }}</textarea>
</div>

<div>
    <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.vehicle_quota') ?? 'Vehicle Quota' }}</label>
    <input name="vehicle_quota" type="number" min="1" max="9999" placeholder="{{ __('admin_dashboard.vehicle_quota_unlimited') ?? 'Leave empty for unlimited' }}"
           value="{{ old('vehicle_quota', $c?->vehicle_quota) }}"
           class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
</div>

<div class="flex gap-2 pt-2">
    <button type="submit" class="dash-btn dash-btn-primary">{{ __('common.save') }}</button>
    <a href="{{ route('admin.customers.index') }}" class="dash-btn dash-btn-secondary">{{ __('common.back') }}</a>
</div>
