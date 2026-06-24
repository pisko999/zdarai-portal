@props([
    'variant' => 'full', // 'full' = mark + wordmark, 'mark' = jen čtvereček AI
])

@php
    // Konzistentní brand barvy (Dark Tech): green-400 #22c55e, gray-950 #0a0a0a
    $mono = "'JetBrains Mono','SFMono-Regular',ui-monospace,Menlo,monospace";
@endphp

@if ($variant === 'mark')
    <svg {{ $attributes->merge(['class' => 'h-8 w-8', 'role' => 'img', 'aria-label' => 'ŽďárAI']) }}
         viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="40" height="40" rx="8" fill="#22c55e"/>
        <text x="20" y="21" text-anchor="middle" dominant-baseline="central"
              font-family="{{ $mono }}" font-size="16" font-weight="800"
              letter-spacing="-0.5" fill="#0a0a0a">AI</text>
    </svg>
@else
    <svg {{ $attributes->merge(['class' => 'h-8 w-auto', 'role' => 'img', 'aria-label' => 'ŽďárAI']) }}
         viewBox="0 0 184 40" xmlns="http://www.w3.org/2000/svg">
        {{-- Mark --}}
        <rect x="0" y="0" width="40" height="40" rx="8" fill="#22c55e"/>
        <text x="20" y="21" text-anchor="middle" dominant-baseline="central"
              font-family="{{ $mono }}" font-size="16" font-weight="800"
              letter-spacing="-0.5" fill="#0a0a0a">AI</text>
        {{-- Wordmark --}}
        <text x="50" y="21" dominant-baseline="central"
              font-family="{{ $mono }}" font-size="25" font-weight="800" letter-spacing="-1">
            <tspan fill="#22c55e">Žďár</tspan><tspan fill="currentColor">AI</tspan>
        </text>
    </svg>
@endif
