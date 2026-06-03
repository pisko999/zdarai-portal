@props(['title' => null, 'description' => null, 'ogType' => 'website', 'ogImage' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ŽďárAI — AI události pro vývojáře' }}</title>
    {{-- SEO Meta --}}
    <meta name="description" content="{{ $description ?? 'Měsíční AI přednášky pro vývojáře v Hernici ve Žďáru nad Sázavou. Každý první čtvrtek v měsíci.' }}">
    <meta name="keywords" content="AI, umělá inteligence, přednášky, vývojáři, Žďár nad Sázavou, programování">
    <meta name="author" content="ŽďárAI">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $title ?? 'ŽďárAI — AI události pro vývojáře' }}">
    <meta property="og:description" content="{{ $description ?? 'Měsíční AI přednášky pro vývojáře v Hernici ve Žďáru nad Sázavou.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="ŽďárAI">
    <meta property="og:locale" content="{{ app()->getLocale() === 'cs' ? 'cs_CZ' : 'en_US' }}">
    @isset($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    @endisset

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title ?? 'ŽďárAI' }}">
    <meta name="twitter:description" content="{{ $description ?? 'Měsíční AI přednášky pro vývojáře ve Žďáru nad Sázavou.' }}">

    <link rel="alternate" hreflang="cs" href="{{ url('/lang/cs') }}">
    <link rel="alternate" hreflang="en" href="{{ url('/lang/en') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen antialiased overflow-x-hidden">
    <!-- Navigation -->
    <nav x-data="{ open: false }" class="border-b border-gray-800 bg-gray-950/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <div class="w-8 h-8 bg-green-400 rounded flex items-center justify-center flex-shrink-0">
                    <span class="text-gray-950 font-black text-xs mono">AI</span>
                </div>
                <span class="text-xl font-black tracking-tight">
                    <span class="text-green-400 glow-text">Žďár</span><span class="text-white">AI</span>
                </span>
                <span class="mono text-xs text-green-400/50 ml-1">v2026</span>
            </a>

            <!-- Desktop menu -->
            <div class="hidden sm:flex items-center gap-6 text-sm">
                <a href="/" class="text-sm text-gray-400 hover:text-green-400 transition mono">./udalosti</a>
                <a href="/o-nas" class="text-sm text-gray-400 hover:text-green-400 transition mono">./o-nas</a>
                <a href="/archiv" class="text-sm text-gray-400 hover:text-green-400 transition mono">./archiv</a>
                <div class="flex gap-1">
                    @if(app()->getLocale() === 'cs')
                        <span class="mono text-xs px-2 py-1 bg-green-400 text-gray-950 font-bold rounded">CZ</span>
                        <a href="{{ route('lang.switch', 'en') }}" class="mono text-xs px-2 py-1 text-gray-500 hover:text-gray-300 transition">EN</a>
                    @else
                        <a href="{{ route('lang.switch', 'cs') }}" class="mono text-xs px-2 py-1 text-gray-500 hover:text-gray-300 transition">CZ</a>
                        <span class="mono text-xs px-2 py-1 bg-green-400 text-gray-950 font-bold rounded">EN</span>
                    @endif
                </div>
                @auth
                    <a href="{{ route('my-registrations') }}" class="text-gray-400 hover:text-green-400 text-xs transition mono">{{ auth()->user()->name }}</a>
                    <a href="{{ route('profile.edit') }}" class="text-gray-400 hover:text-green-400 text-xs transition mono">profil</a>
                    <form method="POST" action="/logout" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-300 text-xs transition mono">odhlásit</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-400 hover:text-green-400 text-xs border border-gray-700 hover:border-green-500/50 px-2 py-1 rounded transition mono">přihlásit</a>
                @endauth
            </div>

            <!-- Hamburger button -->
            <button @click="open = !open" class="sm:hidden text-gray-400 hover:text-green-400 transition p-1" aria-label="Menu">
                <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile menu -->
        <div x-show="open" x-transition class="sm:hidden border-t border-gray-800 bg-gray-950 px-6 py-4 space-y-4">
            <a href="/" class="block text-gray-400 hover:text-green-400 transition mono text-sm py-1">./udalosti</a>
            <a href="/o-nas" class="block text-gray-400 hover:text-green-400 transition mono text-sm py-1">./o-nas</a>
            <a href="/archiv" class="block text-gray-400 hover:text-green-400 transition mono text-sm py-1">./archiv</a>
            <div class="flex gap-2 pt-1">
                @if(app()->getLocale() === 'cs')
                    <span class="mono text-xs px-2 py-1 bg-green-400 text-gray-950 font-bold rounded">CZ</span>
                    <a href="{{ route('lang.switch', 'en') }}" class="mono text-xs px-2 py-1 text-gray-500 hover:text-gray-300 transition">EN</a>
                @else
                    <a href="{{ route('lang.switch', 'cs') }}" class="mono text-xs px-2 py-1 text-gray-500 hover:text-gray-300 transition">CZ</a>
                    <span class="mono text-xs px-2 py-1 bg-green-400 text-gray-950 font-bold rounded">EN</span>
                @endif
            </div>
            <div class="border-t border-gray-800 pt-4 space-y-3">
                @auth
                    <a href="{{ route('my-registrations') }}" class="block text-gray-400 hover:text-green-400 text-sm transition mono py-1">{{ auth()->user()->name }}</a>
                    <a href="{{ route('profile.edit') }}" class="block text-gray-400 hover:text-green-400 text-sm transition mono py-1">profil</a>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-300 text-sm transition mono py-1">odhlásit</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="inline-block text-gray-400 hover:text-green-400 text-sm border border-gray-700 hover:border-green-500/50 px-3 py-1.5 rounded transition mono">přihlásit</a>
                @endauth
            </div>
        </div>
    </nav>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-t border-gray-800 mt-20">
        <div class="max-w-6xl mx-auto px-6 py-8">
            <div class="flex flex-col items-center sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-center sm:text-left">
                    <div class="font-black text-lg"><span class="text-green-400">Žďár</span>AI</div>
                </div>
                <div class="mono text-xs text-gray-600 text-center">Hernice · Žďár nad Sázavou</div>
                <div class="mono text-xs text-gray-700 text-center sm:text-right">
                    <div>© {{ date('Y') }} <a href="https://www.mtgforfun.cz" target="_blank" rel="noopener" class="hover:text-gray-500 transition">MtgForFun</a></div>
                    <div class="mt-1">Built with 🤖 AI + TaskForge</div>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
