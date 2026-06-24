@props(['title' => null, 'description' => null, 'ogType' => 'website', 'ogImage' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ŽďárAI — AI události pro vývojáře' }}</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#0a0a0a">
    {{-- SEO Meta --}}
    <meta name="description" content="{{ $description ?? 'Měsíční AI přednášky pro programátory a AI nadšence v IT NeŠkole ve Žďáru nad Sázavou. Většinou první čtvrtek v měsíci.' }}">
    <meta name="keywords" content="AI, umělá inteligence, přednášky, vývojáři, Žďár nad Sázavou, programování">
    <meta name="author" content="ŽďárAI">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $title ?? 'ŽďárAI — AI události pro vývojáře' }}">
    <meta property="og:description" content="{{ $description ?? 'Měsíční AI přednášky pro programátory a AI nadšence v IT NeŠkole ve Žďáru nad Sázavou.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="ŽďárAI">
    <meta property="og:locale" content="{{ app()->getLocale() === 'cs' ? 'cs_CZ' : 'en_US' }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('og-default.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="ŽďárAI — AI události pro vývojáře">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'ŽďárAI' }}">
    <meta name="twitter:description" content="{{ $description ?? 'Měsíční AI přednášky pro vývojáře ve Žďáru nad Sázavou.' }}">
    <meta name="twitter:image" content="{{ $ogImage ?? asset('og-default.png') }}">

    <link rel="alternate" hreflang="cs" href="{{ url('/lang/cs') }}">
    <link rel="alternate" hreflang="en" href="{{ url('/lang/en') }}">

    {{-- Structured data: Organization (site-wide). Event schema se přidává na detailu akce. --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'ŽďárAI',
        'url' => config('app.url'),
        'logo' => asset('og-default.png'),
        'description' => 'Měsíční AI přednášky pro programátory a AI nadšence v IT NeŠkole ve Žďáru nad Sázavou.',
        'email' => 'klara@zdarai.cz',
        'location' => [
            '@type' => 'Place',
            'name' => 'IT NeŠkola',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Dolní 1',
                'addressLocality' => 'Žďár nad Sázavou',
                'addressCountry' => 'CZ',
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Google Analytics 4 — vkládá se jen se souhlasem (a jen je-li GOOGLE_ANALYTICS_ID nastaveno) --}}
    @include('partials.analytics')
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen antialiased overflow-x-hidden">
    <!-- Navigation -->
    <nav x-data="{ open: false }" class="border-b border-gray-800 bg-gray-950/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center" aria-label="ŽďárAI — domů">
                <x-logo class="h-8 w-auto text-white glow-text" />
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
                    <x-logo class="h-7 w-auto text-white" />
                </div>
                <div class="mono text-xs text-gray-600 text-center">IT NeŠkola · Žďár nad Sázavou</div>
                <div class="mono text-xs text-gray-700 text-center sm:text-right">
                    <div>© {{ date('Y') }} <a href="https://www.mtgforfun.cz" target="_blank" rel="noopener" class="hover:text-gray-500 transition">MtgForFun</a></div>
                    <div class="mt-1">Built with 🤖 AI + TaskForge</div>
                    <div class="mt-1">
                        <button type="button"
                            onclick="window.dispatchEvent(new CustomEvent('open-cookie-settings'))"
                            class="hover:text-gray-500 transition underline decoration-dotted">{{ __('messages.cookies.footer_link') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <livewire:cookie-consent />
    @auth
        <livewire:newsletter-prompt />
    @endauth

    @livewireScripts
</body>
</html>
