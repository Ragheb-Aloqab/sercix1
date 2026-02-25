@extends('admin.layouts.app')

@section('title', __('common.edit') . ' ' . $announcement->title . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('common.edit') . ' ' . \Illuminate\Support\Str::limit($announcement->title, 30))

@section('content')
<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-2xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="dash-page-title">{{ __('common.edit') }}</h1>
            <a href="{{ route('admin.announcements.index') }}" class="dash-btn dash-btn-secondary">{{ __('common.back') }}</a>
        </div>

        <div class="dash-card">
            <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.title') }} *</label>
                    <input name="title" value="{{ old('title', $announcement->title) }}" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                    @error('title')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.body') }} *</label>
                    <textarea name="body" rows="5" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">{{ old('body', $announcement->body) }}</textarea>
                    @error('body')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.target') }} *</label>
                    <select name="target_type" id="target_type" class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                        <option value="all" {{ old('target_type', $announcement->target_type) === 'all' ? 'selected' : '' }}>{{ __('admin_dashboard.target_all') }}</option>
                        <option value="selected" {{ old('target_type', $announcement->target_type) === 'selected' ? 'selected' : '' }}>{{ __('admin_dashboard.target_selected') }}</option>
                    </select>
                </div>
                <div id="companies_select" class="{{ old('target_type', $announcement->target_type) === 'selected' ? '' : 'hidden' }}">
                    <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.select_companies') }}</label>
                    <div class="mt-2 max-h-48 overflow-y-auto space-y-2 p-3 rounded-xl bg-slate-800/30">
                        @foreach($companies as $c)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="target_company_ids[]" value="{{ $c->id }}"
                                    {{ in_array($c->id, old('target_company_ids', $announcement->target_company_ids ?? [])) ? 'checked' : '' }}
                                    class="rounded accent-sky-500">
                                <span class="text-slate-300">{{ $c->company_name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_published" value="1" {{ old('is_published', $announcement->is_published) ? 'checked' : '' }} class="rounded accent-sky-500">
                        <span class="text-slate-300">{{ __('admin_dashboard.published') }}</span>
                    </label>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="dash-btn dash-btn-primary">{{ __('common.save') }}</button>
                    <a href="{{ route('admin.announcements.index') }}" class="dash-btn dash-btn-secondary">{{ __('common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('target_type').addEventListener('change', function() {
        document.getElementById('companies_select').classList.toggle('hidden', this.value !== 'selected');
    });
</script>
@endsection
