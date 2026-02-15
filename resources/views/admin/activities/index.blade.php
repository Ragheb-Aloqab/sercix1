@extends('admin.layouts.app')

@section('title', __('activities.title') . ' | SERV.X')
@section('page_title', __('activities.page_title'))

@section('content')

    <div class="space-y-4">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-black tracking-tight">
                {{ __('activities.page_title') }}
            </h2>
        </div>

        {{-- Table Card --}}
        <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden">

            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="p-3 text-start">#</th>
                        <th class="p-3 text-start">{{ __('activities.actor') }}</th>
                        <th class="p-3 text-start">{{ __('activities.action') }}</th>
                        <th class="p-3 text-start">{{ __('activities.on') }}</th>
                        <th class="p-3 text-start">{{ __('activities.description') }}</th>
                        <th class="p-3 text-start">{{ __('activities.time') }}</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200/70">

                    @forelse ($activities as $activity)
                        <tr class="hover:bg-slate-50 transition">

                            {{-- ID --}}
                            <td class="p-3 font-bold text-slate-600">
                                #{{ $activity->id }}
                            </td>

                            {{-- Actor --}}
                            <td class="p-3">
                                @php
                                    $actorMap = [
                                        'admin' => [__('activities.actor_admin'), 'bg-sky-50 text-sky-700'],
                                        'technician' => [__('activities.actor_technician'), 'bg-emerald-50 text-emerald-700'],
                                        'company' => [__('activities.actor_company'), 'bg-indigo-50 text-indigo-700'],
                                        'customer' => [__('activities.actor_customer'), 'bg-amber-50 text-amber-700'],
                                        'system' => [__('activities.actor_system'), 'bg-slate-100 text-slate-700'],
                                    ];
                                    [$actorLabel, $actorClass] = $actorMap[$activity->actor_type] ?? [
                                        __('activities.actor_unknown'),
                                        'bg-slate-100 text-slate-700',
                                    ];
                                @endphp

                                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $actorClass }}">
                                    {{ $actorLabel }}
                                </span>
                            </td>

                            {{-- Action --}}
                            <td class="p-3 font-semibold">
                                @php
                                    $actionKey = 'activities.action_' . $activity->action;
                                @endphp
                                {{ \Illuminate\Support\Str::startsWith(__($actionKey), 'activities.') ? $activity->action : __($actionKey) }}
                            </td>

                            {{-- Subject --}}
                            <td class="p-3">
                                @php
                                    $subjectKey = 'activities.subject_' . $activity->subject_type;
                                @endphp
                                <span class="font-bold">
                                    {{ \Illuminate\Support\Str::startsWith(__($subjectKey), 'activities.') ? $activity->subject_type : __($subjectKey) }}
                                </span>
                                <span class="text-slate-500">
                                    #{{ $activity->subject_id }}
                                </span>
                            </td>

                            {{-- Description --}}
                            <td class="p-3 text-slate-600 max-w-md">
                                {{ $activity->description }}
                            </td>

                            {{-- Time --}}
                            <td class="p-3 text-xs text-slate-500 whitespace-nowrap">
                                {{ $activity->created_at->diffForHumans() }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-500">
                                {{ __('activities.no_activities') }}
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div>
            {{ $activities->links() }}
        </div>

    </div>

@endsection
