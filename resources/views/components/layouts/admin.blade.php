<!DOCTYPE html>
<html lang="cs" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} — ŽďárAI</title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-950 text-green-300 font-mono antialiased min-h-screen">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="w-56 bg-gray-900 border-r border-green-900/30 flex flex-col">
            <div class="p-4 border-b border-green-900/30">
                <a href="/admin" class="text-green-400 font-bold text-lg tracking-wider">&gt;_ Admin</a>
                <div class="text-green-800 text-xs mt-0.5">ŽďárAI portál</div>
            </div>
            <nav class="p-3 space-y-1 flex-1">
                <a href="/admin" class="{{ request()->is('admin') ? 'bg-green-950 text-green-300' : 'text-green-700 hover:text-green-500 hover:bg-green-950/50' }} block px-3 py-2 rounded text-sm transition">📊 Dashboard</a>
                <a href="/admin/events" class="{{ request()->is('admin/events*') ? 'bg-green-950 text-green-300' : 'text-green-700 hover:text-green-500 hover:bg-green-950/50' }} block px-3 py-2 rounded text-sm transition">📅 Události</a>
                <a href="/admin/talks" class="{{ request()->is('admin/talks*') ? 'bg-green-950 text-green-300' : 'text-green-700 hover:text-green-500 hover:bg-green-950/50' }} block px-3 py-2 rounded text-sm transition">🎙️ Přednášky</a>
                <a href="/admin/speakers" class="{{ request()->is('admin/speakers*') ? 'bg-green-950 text-green-300' : 'text-green-700 hover:text-green-500 hover:bg-green-950/50' }} block px-3 py-2 rounded text-sm transition">👤 Přednášející</a>
                <a href="/admin/registrations" class="{{ request()->is('admin/registrations*') ? 'bg-green-950 text-green-300' : 'text-green-700 hover:text-green-500 hover:bg-green-950/50' }} block px-3 py-2 rounded text-sm transition">📋 Registrace</a>
            </nav>
            <div class="p-3 border-t border-green-900/30">
                <div class="text-xs text-green-800 mb-2">{{ auth()->user()->email }}</div>
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="text-xs text-red-800 hover:text-red-600 transition">Odhlásit →</button>
                </form>
            </div>
        </aside>

        {{-- Main --}}
        <main class="flex-1 overflow-auto">
            <div class="p-8">
                {{ $slot }}
            </div>
        </main>
    </div>
    @livewireScripts
</body>
</html>
