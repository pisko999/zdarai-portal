<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ŽďárAI — AI události pro vývojáře' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-950 text-green-300 font-mono antialiased min-h-screen">
    <!-- Navigation -->
    <nav class="border-b border-green-900/30 bg-gray-950/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/" class="text-green-400 font-bold text-xl tracking-widest hover:text-green-300 transition">
                &gt;_ <span class="text-white">ŽďárAI</span>
            </a>
            <div class="flex items-center gap-6 text-sm">
                <a href="/" class="text-green-600 hover:text-green-400 transition">Události</a>
                <a href="/o-nas" class="text-green-600 hover:text-green-400 transition">O nás</a>
                <a href="/archiv" class="text-green-600 hover:text-green-400 transition">Archiv</a>
                @auth
                    <a href="{{ route('my-registrations') }}" class="text-green-700 hover:text-green-500 text-xs transition">{{ auth()->user()->name }}</a>
                    <a href="{{ route('profile.edit') }}" class="text-green-800 hover:text-green-600 text-xs transition">profil</a>
                    <form method="POST" action="/logout" class="inline">
                        @csrf
                        <button type="submit" class="text-green-900 hover:text-green-700 text-xs transition">odhlásit</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-green-800 hover:text-green-600 text-xs border border-green-900/40 px-2 py-1 rounded transition">přihlásit</a>
                @endauth
            </div>
        </div>
    </nav>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-t border-green-900/30 mt-20 py-8 text-center text-green-800 text-xs">
        ŽďárAI &nbsp;·&nbsp; MtgForFun, Žďár nad Sázavou &nbsp;·&nbsp; každý první čtvrtek v měsíci
    </footer>

    @livewireScripts
</body>
</html>
