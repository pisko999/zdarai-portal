<div>
    {{-- Výběr šablony a akce --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <label class="block">
            <span class="block text-xs uppercase font-bold text-green-700 mb-1">Šablona</span>
            <select wire:model.live="template"
                    class="w-full bg-gray-900 border border-green-900/40 rounded px-3 py-2 text-sm text-green-300">
                @foreach($groupedTemplates as $group => $items)
                    <optgroup label="{{ $group }}">
                        @foreach($items as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="block text-xs uppercase font-bold text-green-700 mb-1">Akce (data pro náhled)</span>
            <select wire:model.live="eventId"
                    class="w-full bg-gray-900 border border-green-900/40 rounded px-3 py-2 text-sm text-green-300 disabled:opacity-40"
                    @disabled($events->isEmpty())>
                @forelse($events as $event)
                    <option value="{{ $event->id }}">{{ $event->title }} ({{ $event->date->format('j. n. Y') }})</option>
                @empty
                    <option value="">Žádná akce v databázi</option>
                @endforelse
            </select>
        </label>
    </div>

    {{-- Poznámka k šabloně --}}
    @if(isset(\App\Livewire\Admin\NewsletterPreview::TEMPLATES[$template]))
        <p class="text-xs text-green-800 mb-6 leading-relaxed">
            ℹ️ {{ \App\Livewire\Admin\NewsletterPreview::TEMPLATES[$template]['note'] }}
            Náhled je jen ke čtení — nic se neodesílá ani neukládá, příjemce je smyšlený.
        </p>
    @endif

    @if($preview === null)
        <div class="border border-green-900/40 rounded-lg p-6 bg-gray-900/30 text-sm text-green-700">
            Pro náhled této šablony je potřeba vybrat akci. V databázi zatím žádná není.
        </div>
    @elseif($preview['error'] !== null)
        <div class="border border-red-900/60 rounded-lg p-6 bg-red-950/30">
            <div class="text-xs uppercase font-bold text-red-500 mb-2">Šablonu se nepodařilo vyrenderovat</div>
            <p class="text-sm text-red-300 break-words">{{ $preview['error'] }}</p>
            <p class="text-xs text-red-800 mt-3">
                Obvyklá příčina: akce nemá vyplněná data, která mail očekává (např. přednáška bez přednášejícího).
            </p>
        </div>
    @else
        {{-- Předmět --}}
        <div class="border border-green-900/40 rounded-lg bg-gray-900/30 mb-4">
            <div class="px-4 py-3 border-b border-green-900/30">
                <div class="text-xs uppercase font-bold text-green-700 mb-1">Předmět</div>
                <div class="text-sm text-green-300 break-words">{{ $preview['subject'] }}</div>
            </div>

            {{-- Přílohy --}}
            <div class="px-4 py-3">
                <div class="text-xs uppercase font-bold text-green-700 mb-1">Přílohy</div>
                @if($preview['attachments'] === [])
                    <div class="text-sm text-green-800">Bez příloh</div>
                @else
                    <ul class="text-sm text-green-300 space-y-1">
                        @foreach($preview['attachments'] as $attachment)
                            <li>📎 {{ $attachment['name'] }} <span class="text-green-800">({{ $attachment['size'] }})</span></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Náhled v izolovaném iframu: mail má vlastní <html> a tmavé styly,
             vložení přímo do stránky by rozbilo layout administrace. --}}
        <div class="border border-green-900/40 rounded-lg overflow-hidden bg-white">
            <iframe
                wire:key="preview-{{ $template }}-{{ $eventId }}"
                title="Náhled e-mailu"
                sandbox=""
                class="w-full h-[800px] border-0 block"
                srcdoc="{{ $preview['html'] }}"></iframe>
        </div>
    @endif
</div>
