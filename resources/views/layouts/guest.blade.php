<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'ŽďárAI') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-gray-950 text-gray-100 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-950">
            <div>
                <a href="/" class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-green-400 rounded flex items-center justify-center">
                        <span class="text-gray-950 font-black text-sm mono">AI</span>
                    </div>
                    <span class="text-xl font-black"><span class="text-green-400">Žďár</span>AI</span>
                </a>
            </div>
            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-gray-900 border border-gray-800 shadow-xl overflow-hidden sm:rounded-xl">
                {{ $slot }}
            </div>
        </div>
        @livewireScripts
    </body>
</html>
