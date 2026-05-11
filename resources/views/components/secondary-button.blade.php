<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-5 py-2.5 bg-transparent border border-gray-700 rounded-lg font-medium text-sm text-gray-400 hover:text-gray-200 hover:border-gray-500 focus:outline-none transition']) }}>
    {{ $slot }}
</button>
