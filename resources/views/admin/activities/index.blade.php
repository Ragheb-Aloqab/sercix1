@extends('admin.layouts.app')

@section('title', 'سجل الأنشطة | SERV.X')
@section('page_title', 'سجل الأنشطة')

@section('content')

    <div class="space-y-4">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-black tracking-tight">
                سجل الأنشطة
            </h2>
        </div>

        {{-- Table Card --}}
        <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden">

            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="p-3 text-start">#</th>
                        <th class="p-3 text-start">الفاعل</th>
                        <th class="p-3 text-start">العملية</th>
                        <th class="p-3 text-start">على</th>
                        <th class="p-3 text-start">الوصف</th>
                        <th class="p-3 text-start">الوقت</th>
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
                                        'admin' => ['مدير النظام', 'bg-sky-50 text-sky-700'],
                                        'technician' => ['فني', 'bg-emerald-50 text-emerald-700'],
                                        'company' => ['شركة', 'bg-indigo-50 text-indigo-700'],
                                        'customer' => ['عميل', 'bg-amber-50 text-amber-700'],
                                        'system' => ['النظام', 'bg-slate-100 text-slate-700'],
                                    ];
                                    [$actorLabel, $actorClass] = $actorMap[$activity->actor_type] ?? [
                                        'غير معروف',
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
                                    $actionMap = [
                                        'order_created' => 'إنشاء طلب',
                                        'order_assigned' => 'إسناد طلب',
                                        'payment_paid' => 'دفع مبلغ',
                                    ];
                                @endphp

                                {{ $actionMap[$activity->action] ?? $activity->action }}
                            </td>

                            {{-- Subject --}}
                            <td class="p-3">
                                @php
                                    $subjectMap = [
                                        'order' => 'طلب',
                                        'payment' => 'دفعة',
                                        'user' => 'مستخدم',
                                    ];
                                @endphp

                                <span class="font-bold">
                                    {{ $subjectMap[$activity->subject_type] ?? $activity->subject_type }}
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
                                لا توجد أنشطة مسجلة
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
