<div x-data="{ open: false, url: '' }"
     @open-image-preview.window="open = true; url = $event.detail.url"
     @keydown.escape.window="open = false"
     x-show="open"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[60] flex items-center justify-center p-4"
     style="display: none;">
    <div class="fixed inset-0 bg-black/80" @click="open = false"></div>
    <div class="relative max-w-4xl max-h-[90vh] rounded-xl overflow-hidden bg-slate-900 shadow-2xl">
        <button type="button" @click="open = false" class="absolute top-3 end-3 z-10 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <img :src="url" alt="" class="max-w-full max-h-[90vh] object-contain" @click.stop>
    </div>
</div>
