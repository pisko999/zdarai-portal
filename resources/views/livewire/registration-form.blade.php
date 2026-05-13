<div>
    @if($success)
        <div class="border border-green-700 bg-green-950/50 rounded-xl p-8 text-center">
            <div class="text-4xl mb-4">✅</div>
            <h3 class="text-white font-bold text-xl mb-2">{{ __('messages.registration.success_title') }}</h3>
            <p class="text-green-600 text-sm">{{ __('messages.registration.success_text') }}</p>
        </div>
    @else
        @if($errorMessage)
            <div class="border border-red-800 bg-red-950/30 rounded-lg px-4 py-3 mb-4 text-red-400 text-sm">
                {{ $errorMessage }}
            </div>
        @endif

        <form wire:submit.prevent="register" class="border border-gray-800 rounded-xl p-6 bg-gray-900/20 space-y-4">
            <div>
                <label class="block text-gray-500 text-xs font-bold uppercase mb-1" for="name">{{ __('messages.registration.name') }} *</label>
                <input id="name" type="text" wire:model="name"
                    class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 text-gray-100 placeholder-gray-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500/50 transition text-sm"
                    placeholder="Jan Novák" autocomplete="name">
                @error('name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-gray-500 text-xs font-bold uppercase mb-1" for="email">{{ __('messages.registration.email') }} *</label>
                <input id="email" type="email" wire:model="email"
                    class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 text-gray-100 placeholder-gray-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500/50 transition text-sm"
                    placeholder="jan@example.com" autocomplete="email">
                @error('email') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-gray-500 text-xs font-bold uppercase mb-1" for="ai_level">{{ __('messages.registration.ai_level') }} <span class="text-gray-700 font-normal normal-case">({{ __('messages.registration.optional') }})</span></label>
                <select id="ai_level" wire:model="ai_level"
                    class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 text-gray-100 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500/50 transition text-sm mono">
                    <option value="">— {{ __('messages.registration.ai_level_placeholder') }} —</option>
                    <option value="beginner">{{ __('messages.registration.ai_level_beginner') }}</option>
                    <option value="intermediate">{{ __('messages.registration.ai_level_intermediate') }}</option>
                    <option value="advanced">{{ __('messages.registration.ai_level_advanced') }}</option>
                    <option value="expert">{{ __('messages.registration.ai_level_expert') }}</option>
                </select>
                @error('ai_level') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-gray-500 text-xs font-bold uppercase mb-1" for="organization">{{ __('messages.registration.organization') }} <span class="text-gray-700 font-normal normal-case">({{ __('messages.registration.optional') }})</span></label>
                <input id="organization" type="text" wire:model="organization"
                    class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 text-gray-100 placeholder-gray-700 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500/50 transition text-sm"
                    placeholder="{{ __('messages.registration.organization_placeholder') }}">
                @error('organization') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-start gap-3">
                <input id="email_opt_in" type="checkbox" wire:model="email_opt_in"
                    class="mt-0.5 accent-green-500" checked>
                <label for="email_opt_in" class="text-gray-500 text-xs leading-relaxed">
                    {{ __('messages.registration.email_opt_in') }}
                </label>
            </div>

            <button type="submit"
                wire:loading.attr="disabled"
                class="w-full bg-green-400 hover:bg-green-300 disabled:opacity-50 text-gray-950 font-bold py-3 px-6 rounded-lg transition text-sm">
                <span wire:loading.remove>{{ __('messages.registration.submit') }}</span>
                <span wire:loading>{{ __('messages.registration.sending') }}</span>
            </button>

            <p class="text-gray-700 text-xs text-center">
                {{ __('messages.registration.privacy') }}
            </p>
        </form>
    @endif
</div>
