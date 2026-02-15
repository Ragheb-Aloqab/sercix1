<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-black">{{ __('livewire.company_settings') }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ __('livewire.company_settings_desc') }}
        </p>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Profile --}}
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 p-5">
        <h2 class="font-black mb-4">{{ __('livewire.company_data') }}</h2>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-sm font-bold">{{ __('livewire.company_name') }}</label>
                <input wire:model.defer="name" type="text"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
                @error('name')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold">{{ __('livewire.email') }}</label>
                <input wire:model.defer="email" type="email"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
                @error('email')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold">{{ __('livewire.phone_label') }}</label>
                <input wire:model.defer="phone" type="text"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
                @error('phone')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <button wire:click="saveProfile"
                class="px-4 py-2 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold">
                {{ __('livewire.save_changes') }}
            </button>
        </div>
    </div>

    {{-- Password --}}
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 p-5">
        <h2 class="font-black mb-4">{{ __('livewire.change_password') }}</h2>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-sm font-bold">{{ __('livewire.current_password') }}</label>
                <input wire:model.defer="current_password" type="password"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
                @error('current_password')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold">{{ __('livewire.new_password') }}</label>
                <input wire:model.defer="password" type="password"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
                @error('password')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold">{{ __('livewire.confirm_password') }}</label>
                <input wire:model.defer="password_confirmation" type="password"
                    class="mt-2 w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent px-4 py-3 outline-none">
            </div>
        </div>

        <div class="mt-4">
            <button wire:click="changePassword" class="px-4 py-2 rounded-2xl bg-emerald-600 text-white font-bold">
                {{ __('livewire.update_password') }}
            </button>
        </div>
    </div>

</div>
