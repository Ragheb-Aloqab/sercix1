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

@if(isset($plans) && $plans->isNotEmpty())
<div>
    <label class="text-xs font-semibold text-slate-400">{{ __('plans.title') }}</label>
    <select name="plan_id" class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
        <option value="">{{ __('common.none') ?? 'None' }}</option>
        @foreach($plans as $plan)
            <option value="{{ $plan->id }}" {{ old('plan_id', $c?->plan_id) == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
        @endforeach
    </select>
</div>
@endif

{{-- White-Label Branding (Super Admin) --}}
<div class="border-t border-slate-600/50 pt-6 mt-6">
    <h3 class="text-sm font-bold text-slate-300 mb-4">{{ __('admin_dashboard.white_label_branding') ?? 'White-Label Branding' }}</h3>
    <div class="space-y-4">
        <div class="flex items-center gap-2">
            <input type="checkbox" name="white_label_enabled" value="1" id="white_label_enabled" {{ old('white_label_enabled', $c?->white_label_enabled) ? 'checked' : '' }}
                   class="rounded accent-sky-500">
            <label for="white_label_enabled" class="text-sm text-slate-300">{{ __('admin_dashboard.white_label_enabled') ?? 'Enable White-Label Subdomain' }}</label>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.subdomain') ?? 'Subdomain' }}</label>
            <div class="flex items-center gap-2 mt-1">
                <input name="subdomain" value="{{ old('subdomain', $c?->subdomain) }}"
                       placeholder="{{ __('admin_dashboard.subdomain_auto_hint') ?? 'Leave empty to auto-generate from company name' }}"
                       class="flex-1 rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
                <span class="text-slate-500 text-sm">.{{ config('servx.white_label_domain', 'servxmotors.com') }}</span>
            </div>
            @error('subdomain')
                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.primary_color') ?? 'Primary Color' }}</label>
                <div class="flex gap-2 mt-1">
                    <input type="color" value="{{ old('primary_color', $c?->primary_color ?? '#2563eb') }}"
                           class="w-12 h-10 rounded-lg cursor-pointer border border-slate-600 bg-slate-800"
                           onchange="this.nextElementSibling.value=this.value">
                    <input name="primary_color" type="text" value="{{ old('primary_color', $c?->primary_color ?? '#2563eb') }}"
                           class="flex-1 rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50 font-mono text-sm"
                           onchange="this.previousElementSibling.value=this.value"
                           placeholder="#2563eb">
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.secondary_color') ?? 'Secondary Color' }}</label>
                <div class="flex gap-2 mt-1">
                    <input type="color" value="{{ old('secondary_color', $c?->secondary_color ?? '#16a34a') }}"
                           class="w-12 h-10 rounded-lg cursor-pointer border border-slate-600 bg-slate-800"
                           onchange="this.nextElementSibling.value=this.value">
                    <input name="secondary_color" type="text" value="{{ old('secondary_color', $c?->secondary_color ?? '#16a34a') }}"
                           class="flex-1 rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50 font-mono text-sm"
                           onchange="this.previousElementSibling.value=this.value"
                           placeholder="#16a34a">
                </div>
            </div>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.company_logo') ?? 'Company Logo' }}</label>
            @if($c?->logo)
                <div class="flex items-center gap-3 mt-2 mb-2">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($c->logo) }}" alt="Logo" class="w-16 h-16 object-contain rounded-lg bg-white/10">
                    <label class="text-sm text-slate-400">
                        <input type="checkbox" name="remove_logo" value="1" class="rounded accent-rose-500"> {{ __('admin_dashboard.remove_logo') ?? 'Remove logo' }}
                    </label>
                </div>
            @endif
            <input name="logo" type="file" accept="image/*"
                   class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-sky-500/20 file:text-sky-400">
            <p class="text-xs text-slate-500 mt-1">{{ __('admin_dashboard.logo_hint') ?? 'PNG, JPG, WEBP. Max 2MB.' }}</p>
        </div>
    </div>
</div>

<div class="flex gap-2 pt-2">
    <button type="submit" class="dash-btn dash-btn-primary">{{ __('common.save') }}</button>
    <a href="{{ route('admin.customers.index') }}" class="dash-btn dash-btn-secondary">{{ __('common.back') }}</a>
</div>
