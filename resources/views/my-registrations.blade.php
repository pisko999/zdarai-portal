<x-layouts.app title="Moje registrace — ŽďárAI">
    <div class="max-w-3xl mx-auto px-4 py-16">
        <h1 class="text-2xl font-bold text-white mb-8">&gt;_ Moje registrace</h1>

        @php
            $registrations = auth()->user()
                ? \App\Models\Registration::where('user_id', auth()->id())
                    ->with('event')
                    ->orderByDesc('created_at')
                    ->get()
                : collect();
        @endphp

        @forelse($registrations as $reg)
            <div class="border border-green-900/40 rounded-lg p-4 mb-3 bg-gray-900/20">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-white font-medium">{{ $reg->event?->title ?? '—' }}</div>
                        <div class="text-green-700 text-sm">{{ $reg->event?->date?->format('j. n. Y H:i') ?? '' }}</div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded font-bold
                        {{ match($reg->payment_status) {
                            'paid' => 'bg-green-900/50 text-green-400',
                            'pending' => 'bg-yellow-900/40 text-yellow-500',
                            'cancelled' => 'bg-red-900/30 text-red-600',
                            default => 'bg-green-950 text-green-700',
                        } }}">
                        {{ match($reg->payment_status) {
                            'paid' => 'Zaplaceno',
                            'pending' => 'Čeká',
                            'cancelled' => 'Zrušeno',
                            default => 'Zdarma',
                        } }}
                    </span>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-green-800">
                <div class="text-3xl mb-4">📋</div>
                <div>Zatím žádné registrace.</div>
                <a href="/" class="inline-block mt-4 text-green-600 hover:text-green-400 text-sm transition">Prohlédnout události →</a>
            </div>
        @endforelse
    </div>
</x-layouts.app>
