<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Nenalezeno | ŽďárAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 text-gray-100 font-mono antialiased min-h-screen flex items-center justify-center px-4">
    <div class="text-center">
        <!-- Velký kód chyby -->
        <p class="text-green-400 text-8xl md:text-9xl font-bold tracking-tighter select-none">404</p>

        <!-- Oddělovač -->
        <div class="mt-4 mb-6 flex items-center justify-center gap-3 text-green-900">
            <span class="h-px w-16 bg-green-900"></span>
            <span class="text-xs tracking-widest uppercase">error</span>
            <span class="h-px w-16 bg-green-900"></span>
        </div>

        <!-- Popis -->
        <h1 class="text-xl md:text-2xl font-semibold text-gray-100 mb-2">Stránka nenalezena</h1>
        <p class="text-gray-500 text-sm mb-8 max-w-sm mx-auto leading-relaxed">
            Stránka, kterou hledáš, neexistuje nebo byla přesunuta.
        </p>

        <!-- CTA -->
        <a href="/"
           class="inline-flex items-center gap-2 px-6 py-3 bg-green-500/10 border border-green-500/30 text-green-400 rounded-lg text-sm font-medium hover:bg-green-500/20 hover:border-green-400/50 transition-all duration-200">
            <span class="text-green-600">&larr;</span>
            Zpět na úvod
        </a>

        <!-- Dekorativní footer text -->
        <p class="mt-12 text-green-950 text-xs tracking-widest select-none">&gt;_ zdarai.cz</p>
    </div>
</body>
</html>
