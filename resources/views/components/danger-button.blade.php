<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-red-600 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wide hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/50 transition']) }}>
    {{ $slot }}
</button>
