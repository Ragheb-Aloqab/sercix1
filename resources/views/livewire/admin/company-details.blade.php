<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-7xl mx-auto space-y-6 sm:space-y-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-center sm:text-start w-full sm:w-auto">
                <div class="flex items-center gap-3 flex-wrap justify-center sm:justify-start">
                    <h1 class="dash-page-title">{{ $company->company_name }}</h1>
                    <span class="px-3 py-1.5 rounded-xl text-sm font-bold {{ $company->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }}">
                        {{ $company->status === 'active' ? __('admin_dashboard.active') : __('admin_dashboard.suspended') }}
                    </span>
                    <button type="button" wire:click="toggleStatus" wire:loading.attr="disabled"
                            class="px-3 py-1.5 rounded-xl text-sm font-bold border {{ $company->status === 'active' ? 'border-amber-500/50 text-amber-400 hover:bg-amber-500/10' : 'border-emerald-500/50 text-emerald-400 hover:bg-emerald-500/10' }} disabled:opacity-70">
                        {{ $company->status === 'active' ? __('admin_dashboard.suspend') : __('admin_dashboard.activate') }}
                    </button>
                </div>
                <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
            </div>
            <div class="flex flex-wrap gap-2 justify-center sm:justify-end">
                <a href="{{ route('admin.companies.index') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
                </a>
            </div>
        </div>

        {{-- White-Label Branding Card --}}
        @if($company->white_label_enabled || $company->subdomain)
        <div class="dash-card">
            <h2 class="dash-section-title">{{ __('admin_dashboard.white_label_branding') ?? 'White-Label Branding' }}</h2>
            <div class="flex flex-wrap items-center gap-4">
                @if($company->subdomain)
                    <div>
                        <p class="text-xs text-slate-400">{{ __('admin_dashboard.subdomain') ?? 'Subdomain' }}</p>
                        <p class="font-semibold text-white">{{ $company->subdomain }}.{{ config('servx.white_label_domain', 'servx.sa') }}</p>
                    </div>
                @endif
                @if($company->logo)
                    <div>
                        <p class="text-xs text-slate-400 mb-1">{{ __('admin_dashboard.company_logo') ?? 'Logo' }}</p>
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($company->logo) }}" alt="Logo" class="w-16 h-16 object-contain rounded-lg bg-white/10">
                    </div>
                @endif
                <div class="flex gap-2 items-center">
                    <div title="Primary" class="w-8 h-8 rounded-lg border border-slate-600" style="background: {{ $company->getResolvedPrimaryColor() }}"></div>
                    <div title="Secondary" class="w-8 h-8 rounded-lg border border-slate-600" style="background: {{ $company->getResolvedSecondaryColor() }}"></div>
                </div>
                <a href="{{ route('admin.customers.edit', $company) }}" class="dash-btn dash-btn-secondary !py-2 !px-3 text-sm">
                    <i class="fa-solid fa-pen"></i>{{ __('common.edit') }}
                </a>
            </div>
        </div>
        @endif

        {{-- Company Info Card --}}
        <div class="dash-card">
            <h2 class="dash-section-title">{{ __('admin_dashboard.company_details') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-slate-400">{{ __('common.company') }}</p>
                    <p class="font-semibold text-white">{{ $company->company_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">{{ __('admin_dashboard.email') ?? 'Email' }}</p>
                    <p class="font-semibold text-white">{{ $company->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">{{ __('admin_dashboard.phone') }}</p>
                    <p class="font-semibold text-white">{{ $company->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">{{ __('admin_dashboard.created_date') }}</p>
                    <p class="font-semibold text-white">{{ $company->created_at?->format('Y-m-d') ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Stats + Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="dash-card dash-card-interactive">
                <h2 class="dash-section-title">{{ __('admin_dashboard.vehicles_growth') }}</h2>
                <div class="dash-chart-container">
                    <div class="dash-chart-bars" role="img" aria-label="{{ __('admin_dashboard.vehicles_growth') }}">
                        @php $maxCount = max(1, collect($vehiclesGrowthData)->max('count')); @endphp
                        @foreach($vehiclesGrowthData as $m)
                            <div class="dash-chart-bar-wrap" title="{{ $m['label'] }}: {{ $m['count'] }}">
                                <div class="dash-chart-bar" style="height: {{ max(8, ($m['count'] / $maxCount) * 100) }}%"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="dash-card dash-card-compact">
                <p class="dash-card-title">{{ __('admin_dashboard.company_vehicles') }}</p>
                <p class="dash-card-value">{{ count($vehicles) }}</p>
            </div>
            <div class="dash-card dash-card-compact">
                <p class="dash-card-title">{{ __('admin_dashboard.company_drivers') }}</p>
                <p class="dash-card-value">{{ count($drivers) }}</p>
            </div>
        </div>

        {{-- Vehicles + Drivers + Activity --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
            {{-- Vehicles --}}
            <div class="xl:col-span-2 dash-card">
                <h2 class="dash-section-title">{{ __('admin_dashboard.company_vehicles') }}</h2>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @forelse($vehicles as $v)
                        <div class="dash-order-row flex items-center justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-white truncate">{{ $v->display_name }}</p>
                                <p class="text-xs text-slate-400">{{ $v->plate_number ?? '-' }} · {{ $v->driver_name ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs {{ $v->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-600/50 text-slate-400' }}">
                                {{ $v->is_active ? __('admin_dashboard.active') : __('admin_dashboard.inactive') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm py-6 text-center">{{ __('admin_dashboard.no_activity') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- Drivers --}}
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('admin_dashboard.company_drivers') }}</h2>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @forelse($drivers as $d)
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-slate-800/30">
                            <span class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-400">
                                <i class="fa-solid fa-user-tie text-xs"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-white">{{ $d['name'] }}</p>
                                <p class="text-xs text-slate-400">{{ $d['phone'] }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm py-6 text-center">{{ __('admin_dashboard.no_activity') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Orders + Invoices + Activity Timeline --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('admin_dashboard.recent_events') }}</h2>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @forelse($recentOrders as $o)
                        <a href="{{ route('admin.orders.show', $o) }}" class="dash-order-row block">
                            <p class="font-semibold text-white">{{ __('dashboard.order') }} #{{ $o->id }}</p>
                            <p class="text-xs text-slate-400">{{ $o->status }} · {{ $o->vehicle?->plate_number ?? '-' }}</p>
                        </a>
                    @empty
                        <p class="text-slate-500 text-sm py-4">{{ __('orders.no_orders') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('company.recent_invoices') }}</h2>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @forelse($recentInvoices as $inv)
                        <div class="dash-invoice-row">
                            <span class="text-white font-medium">{{ $inv->invoice_number ?? '#' . $inv->id }}</span>
                            <span class="text-slate-300 font-semibold">{{ number_format((float)($inv->total ?? 0), 2) }} {{ __('company.sar') }}</span>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm py-4">{{ __('company.no_invoices_yet') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('admin_dashboard.activity_timeline') }}</h2>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @forelse($activityTimeline as $item)
                        <div class="flex gap-3 p-2 rounded-lg">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0
                                {{ $item['type'] === 'order' ? 'bg-amber-500/20 text-amber-400' : 'bg-emerald-500/20 text-emerald-400' }}">
                                @if($item['type'] === 'order')
                                    <i class="fa-solid fa-receipt text-[10px]"></i>
                                @else
                                    <i class="fa-solid fa-car text-[10px]"></i>
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-white">{{ $item['title'] }}</p>
                                <p class="text-xs text-slate-400">{{ $item['description'] }}</p>
                                <p class="text-xs text-slate-500">{{ $item['time']->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm py-4">{{ __('admin_dashboard.no_activity') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
