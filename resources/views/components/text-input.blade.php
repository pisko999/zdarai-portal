@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-gray-950 border border-gray-700 focus:border-green-500 focus:ring-1 focus:ring-green-500/50 rounded-lg px-4 py-2.5 text-gray-100 placeholder-gray-600 mono text-sm w-full transition outline-none']) }}>
