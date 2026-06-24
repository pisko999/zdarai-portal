<div>
    <div
        x-data="{
            open: @js(! $decided),
            settings: false,
            init() {
                // Otevření z patičky ('Nastavení cookies') i po dříve uděleném souhlasu.
                window.addEventListener('open-cookie-settings', () => {
                    this.open = true;
                    this.settings = true;
                });
                // Po uložení souhlasu lištu zavřeme a přepneme Consent Mode (grant/revoke) bez reloadu.
                Livewire.on('cookie-consent-updated', (e) => {
                    this.open = false;
                    this.settings = false;
                    const analytics = Array.isArray(e) ? e[0]?.analytics : e?.analytics;
                    if (window.zdaraiConsentUpdate) {
                        window.zdaraiConsentUpdate(!!analytics);
                    }
                });
            }
        }"
        x-show="open"
        x-cloak
        x-transition.opacity
        class="fixed inset-x-0 bottom-0 z-[100] p-3 sm:p-4"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('messages.cookies.title') }}"
    >
        <div class="max-w-3xl mx-auto border border-gray-800 bg-gray-950/95 backdrop-blur rounded-xl shadow-2xl p-5 sm:p-6">
            <h2 class="text-white font-bold text-base mb-1 flex items-center gap-2">
                <span class="text-green-400 mono">🍪</span> {{ __('messages.cookies.title') }}
            </h2>
            <p class="text-gray-400 text-xs sm:text-sm leading-relaxed">
                {{ __('messages.cookies.intro') }}
            </p>

            {{-- Panel Nastavení --}}
            <div x-show="settings" x-collapse class="mt-4 space-y-3">
                <div class="flex items-start gap-3 border border-gray-800 rounded-lg p-3 bg-gray-900/30">
                    <input type="checkbox" checked disabled class="mt-0.5 accent-green-500 opacity-60">
                    <div>
                        <div class="text-gray-200 text-sm font-bold">{{ __('messages.cookies.necessary_title') }}</div>
                        <div class="text-gray-500 text-xs leading-relaxed">{{ __('messages.cookies.necessary_desc') }}</div>
                    </div>
                </div>
                <div class="flex items-start gap-3 border border-gray-800 rounded-lg p-3 bg-gray-900/30">
                    <input id="consent-analytics" type="checkbox" wire:model="analytics" class="mt-0.5 accent-green-500">
                    <label for="consent-analytics">
                        <div class="text-gray-200 text-sm font-bold">{{ __('messages.cookies.analytics_title') }}</div>
                        <div class="text-gray-500 text-xs leading-relaxed">{{ __('messages.cookies.analytics_desc') }}</div>
                    </label>
                </div>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-end">
                <button
                    x-show="!settings"
                    type="button"
                    @click="settings = true"
                    class="order-3 sm:order-1 text-gray-400 hover:text-green-400 text-xs mono px-4 py-2.5 transition text-center">
                    {{ __('messages.cookies.settings') }}
                </button>
                <button
                    type="button"
                    wire:click="rejectAll"
                    class="order-2 border border-gray-700 hover:border-gray-500 text-gray-300 text-sm px-4 py-2.5 rounded-lg transition">
                    {{ __('messages.cookies.reject') }}
                </button>
                <button
                    x-show="settings"
                    type="button"
                    wire:click="savePreferences"
                    class="order-2 border border-green-700 hover:border-green-500 text-green-300 text-sm px-4 py-2.5 rounded-lg transition">
                    {{ __('messages.cookies.save') }}
                </button>
                <button
                    type="button"
                    wire:click="acceptAll"
                    class="order-1 sm:order-3 bg-green-400 hover:bg-green-300 text-gray-950 font-bold text-sm px-5 py-2.5 rounded-lg transition">
                    {{ __('messages.cookies.accept') }}
                </button>
            </div>
        </div>
    </div>
</div>
