<x-layouts.app title="Profil — ŽďárAI">
    <div class="max-w-3xl mx-auto px-4 py-16 space-y-6">
        <h1 class="text-2xl font-bold text-white mb-8">&gt;_ Můj profil</h1>

        <div class="border border-green-900/40 rounded-lg p-6 bg-gray-900/30">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="border border-green-900/40 rounded-lg p-6 bg-gray-900/30">
            @include('profile.partials.update-password-form')
        </div>

        <div class="border border-red-900/30 rounded-lg p-6 bg-gray-900/20">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-layouts.app>
