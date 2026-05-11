@props(['title' => null, 'description' => null, 'ogType' => 'website', 'ogImage' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ŽďárAI — AI události pro vývojáře' }}</title>
    {{-- SEO Meta --}}
    <meta name="description" content="{{ $description ?? 'Měsíční AI přednášky pro vývojáře v MtgForFun ve Žďáru nad Sázavou. Každý první čtvrtek v měsíci.' }}">
    <meta name="keywords" content="AI, umělá inteligence, přednášky, vývojáři, Žďár nad Sázavou, programování">
    <meta name="author" content="ŽďárAI">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $title ?? 'ŽďárAI — AI události pro vývojáře' }}">
    <meta property="og:description" content="{{ $description ?? 'Měsíční AI přednášky pro vývojáře v MtgForFun ve Žďáru nad Sázavou.' }}">
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
                <a href="/" class="text-green-600 hover:text-green-400 transition">{{ __("messages.nav.events") }}</a>
                <a href="/o-nas" class="text-green-600 hover:text-green-400 transition">{{ __("messages.nav.about") }}</a>
                <a href="/archiv" class="text-green-600 hover:text-green-400 transition">{{ __("messages.nav.archive") }}</a>
                <a href="{{ route('lang.switch', app()->getLocale() === 'cs' ? 'en' : 'cs') }}"
                   class="text-green-800 hover:text-green-600 text-xs border border-green-900 px-2 py-1 rounded transition">
                    {{ app()->getLocale() === 'cs' ? 'EN' : 'CS' }}
                </a>
            </div>
        </div>
    </nav>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-t border-green-900/30 mt-20 py-8 text-center text-green-800 text-xs">
        {{ __("messages.footer.text") }}
    </footer>

    @livewireScripts
</body>
</html>
