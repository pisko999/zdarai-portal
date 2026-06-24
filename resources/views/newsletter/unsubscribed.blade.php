<x-layouts.app :title="__('messages.newsletter.unsub_title') . ' — ŽďárAI'">
    <div class="max-w-lg mx-auto px-4 py-24 text-center">
        <div class="text-4xl mb-6">✓</div>
        <h1 class="text-white text-2xl font-bold mb-3">{{ __('messages.newsletter.unsub_title') }}</h1>
        <p class="text-green-700">{{ __('messages.newsletter.unsub_text') }}</p>
        <a href="/" class="inline-block mt-6 text-green-600 hover:text-green-400 text-sm transition">{{ __('messages.event.back') }}</a>
    </div>
</x-layouts.app>
