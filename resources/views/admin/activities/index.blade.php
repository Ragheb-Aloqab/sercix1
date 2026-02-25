@extends('admin.layouts.app')

@section('title', __('dashboard.activity_log') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('dashboard.activity_log'))

@section('content')
    <div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
        <div class="dashboard-content max-w-7xl mx-auto space-y-6">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-center sm:text-start w-full sm:w-auto">
                    <h1 class="dash-page-title">{{ __('dashboard.activity_log') }}</h1>
                    <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
                </a>
            </div>

            {{-- Table Card --}}
            <div class="dash-card overflow-hidden p-0">
                <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800/50 border-b border-slate-700">
                    <tr>
                        <th class="p-4 text-start font-semibold text-slate-300">#</th>
                        <th class="p-4 text-start font-semibold text-slate-300">{{ __('activities.actor') }}</th>
                        <th class="p-4 text-start font-semibold text-slate-300">{{ __('activities.action') }}</th>
                        <th class="p-4 text-start font-semibold text-slate-300">{{ __('activities.on') }}</th>
                        <th class="p-4 text-start font-semibold text-slate-300">{{ __('activities.description') }}</th>
                        <th class="p-4 text-start font-semibold text-slate-300">{{ __('activities.time') }}</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-700">

                    @forelse ($activities as $activity)
                        <tr class="hover:bg-slate-800/30 transition-colors">

                            {{-- ID --}}
                            <td class="p-4 font-bold text-slate-300">
                                #{{ $activity->id }}
                            </td>

                            {{-- Actor --}}
                            <td class="p-4">
                                @php
                                    $actorMap = [
                                        'admin' => [__('activities.actor_admin'), 'bg-sky-500/20 text-sky-400'],
                                        'technician' => [__('activities.actor_technician'), 'bg-emerald-500/20 text-emerald-400'],
                                        'company' => [__('activities.actor_company'), 'bg-indigo-500/20 text-indigo-400'],
                                        'customer' => [__('activities.actor_customer'), 'bg-amber-500/20 text-amber-400'],
                                        'system' => [__('activities.actor_system'), 'bg-slate-600/50 text-slate-400'],
                                    ];
                                    [$actorLabel, $actorClass] = $actorMap[$activity->actor_type] ?? [
                                        __('activities.actor_unknown'),
                                        'bg-slate-600/50 text-slate-400',
                                    ];
                                @endphp

                                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $actorClass }}">
                                    {{ $actorLabel }}
                                </span>
                            </td>

                            {{-- Action --}}
                            <td class="p-4 font-semibold text-slate-300">
                                @php
                                    $actionKey = 'activities.action_' . $activity->action;
                                @endphp
                                {{ \Illuminate\Support\Str::startsWith(__($actionKey), 'activities.') ? $activity->action : __($actionKey) }}
                            </td>

                            {{-- Subject --}}
                            <td class="p-4">
                                @php
                                    $subjectKey = 'activities.subject_' . $activity->subject_type;
                                @endphp
                                <span class="font-bold text-white">
                                    {{ \Illuminate\Support\Str::startsWith(__($subjectKey), 'activities.') ? $activity->subject_type : __($subjectKey) }}
                                </span>
                                <span class="text-slate-400">
                                    #{{ $activity->subject_id }}
                                </span>
                            </td>

                            {{-- Description --}}
                            <td class="p-4 text-slate-400 max-w-md">
                                {{ $activity->description }}
                            </td>

                            {{-- Time --}}
                            <td class="p-4 text-xs text-slate-500 whitespace-nowrap">
                                {{ $activity->created_at->diffForHumans() }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                {{ __('activities.no_activities') }}
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
                </div>
                @if($activities->hasPages())
                    <div class="p-4 border-t border-slate-700">
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
