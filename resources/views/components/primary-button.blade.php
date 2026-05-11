<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-green-400 border border-transparent rounded-lg font-bold text-sm text-gray-950 uppercase tracking-wide hover:bg-green-300 focus:outline-none focus:ring-2 focus:ring-green-400/50 transition']) }}>
    {{ $slot }}
</button>
