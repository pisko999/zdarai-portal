<x-layouts.admin title="Dashboard">
    <h1 class="text-2xl font-bold text-white mb-6">&gt;_ Dashboard</h1>
    <div class="grid grid-cols-4 gap-4">
        <div class="border border-green-900/40 rounded-lg p-4 bg-gray-900/30">
            <div class="text-green-700 text-xs uppercase mb-1">Události</div>
            <div class="text-2xl font-bold text-green-400">{{ \App\Models\Event::count() }}</div>
        </div>
        <div class="border border-green-900/40 rounded-lg p-4 bg-gray-900/30">
            <div class="text-green-700 text-xs uppercase mb-1">Přednášky</div>
            <div class="text-2xl font-bold text-green-400">{{ \App\Models\Talk::count() }}</div>
        </div>
        <div class="border border-green-900/40 rounded-lg p-4 bg-gray-900/30">
            <div class="text-green-700 text-xs uppercase mb-1">Přednášející</div>
            <div class="text-2xl font-bold text-green-400">{{ \App\Models\Speaker::count() }}</div>
        </div>
        <div class="border border-green-900/40 rounded-lg p-4 bg-gray-900/30">
            <div class="text-green-700 text-xs uppercase mb-1">Registrace</div>
            <div class="text-2xl font-bold text-green-400">{{ \App\Models\Registration::whereNotIn('payment_status', ['cancelled'])->count() }}</div>
        </div>
    </div>
</x-layouts.admin>
