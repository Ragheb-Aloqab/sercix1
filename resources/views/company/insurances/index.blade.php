@extends('admin.layouts.app')

@section('title', __('fleet.my_insurance') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('fleet.my_insurance'))
@section('subtitle', __('fleet.my_insurance_desc'))

@section('content')
@include('company.partials.glass-start', ['title' => __('fleet.my_insurance')])

<div class="flex flex-col items-center justify-center min-h-[50vh] py-12 sm:py-16">
    <div class="dash-card max-w-md w-full mx-4 text-center">
        <div class="w-20 h-20 rounded-2xl bg-sky-500/20 flex items-center justify-center mx-auto mb-6">
            <i class="fa-solid fa-shield-halved text-4xl text-sky-400"></i>
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-white mb-2">{{ __('company.coming_soon') }}</h2>
        <p class="text-servx-silver">{{ __('company.insurances_coming_soon_desc') }}</p>
    </div>
</div>
@endsection
قبل انتهاء بوليصة التأمين،
 سنعرض لك تلقائيًا أفضل عروض شركات التأمين لتختار الأنسب بكل سهولة.