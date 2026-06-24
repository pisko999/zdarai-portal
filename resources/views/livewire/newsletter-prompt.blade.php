<div>
    @if($show)
        <div
            x-data="{ open: false }"
            x-init="setTimeout(() => open = true, 1200)"
            x-show="open"
            x-cloak
            x-transition.opacity
            class="fixed bottom-3 right-3 left-3 sm:left-auto sm:max-w-sm z-[90]"
            role="dialog"
            aria-modal="false"
            aria-label="{{ __('messages.newsletter.modal_title') }}"
        >
            <div class="border border-gray-800 bg-gray-950/95 backdrop-blur rounded-xl shadow-2xl p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-white font-bold text-sm flex items-center gap-2">
                        <span class="text-green-400 mono">✉</span> {{ __('messages.newsletter.modal_title') }}
                    </h2>
                    <button type="button" wire:click="dismiss" @click="open = false"
                        class="text-gray-600 hover:text-gray-400 transition -mt-1 -mr-1 p-1" aria-label="{{ __('messages.newsletter.later') }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-gray-400 text-xs leading-relaxed mt-2">
                    {{ __('messages.newsletter.modal_text') }}
                </p>
                <div class="mt-4 flex gap-2">
                    <button type="button" wire:click="subscribe" @click="open = false"
                        class="flex-1 bg-green-400 hover:bg-green-300 text-gray-950 font-bold text-xs px-4 py-2.5 rounded-lg transition">
                        {{ __('messages.newsletter.subscribe') }}
                    </button>
                    <button type="button" wire:click="dismiss" @click="open = false"
                        class="border border-gray-700 hover:border-gray-500 text-gray-400 text-xs px-4 py-2.5 rounded-lg transition">
                        {{ __('messages.newsletter.later') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
