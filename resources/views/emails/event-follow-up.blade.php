<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event->title }}</title>
    <style>
        body { font-family: monospace; background: #09090b; color: #f4f4f5; margin: 0; padding: 0; }
        .wrap { max-width: 600px; margin: 0 auto; padding: 40px 20px; }

        /* header */
        .logo { font-size: 22px; font-weight: bold; color: #4ade80; margin: 0 0 2px; }
        .logo span { color: #52525b; }
        .subtitle { color: #71717a; font-size: 12px; margin: 0 0 36px; }

        /* intro */
        .intro { background: #18181b; border: 1px solid #27272a; border-radius: 10px; padding: 26px 28px; margin-bottom: 28px; }
        .intro-badge { display: inline-block; background: #4ade80; color: #09090b; font-size: 10px; font-weight: bold; padding: 3px 10px; border-radius: 3px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
        .intro-title { font-size: 21px; font-weight: bold; color: #f4f4f5; margin: 0 0 16px; line-height: 1.25; }
        .intro-title span { color: #4ade80; }
        .intro p { font-size: 13px; color: #d4d4d8; line-height: 1.7; margin: 0 0 12px; }
        .intro p:last-child { margin-bottom: 0; }

        /* sections */
        .section-label { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #52525b; margin: 32px 0 14px; border-top: 1px solid #27272a; padding-top: 20px; }

        /* recap */
        .talk-card { background: #18181b; border: 1px solid #27272a; border-radius: 8px; padding: 16px 20px; margin-bottom: 12px; }
        .talk-speaker { font-size: 14px; font-weight: bold; color: #4ade80; margin: 0 0 4px; }
        .talk-name { font-size: 13px; font-weight: bold; color: #f4f4f5; margin: 0 0 8px; line-height: 1.35; }
        .talk-desc { font-size: 12px; color: #a1a1aa; line-height: 1.6; margin: 0; }

        /* attachments */
        .attach { background: #18181b; border: 1px solid #4ade8040; border-left: 3px solid #4ade80; border-radius: 8px; padding: 18px 20px; margin-bottom: 12px; }
        .attach-title { font-size: 13px; font-weight: bold; color: #f4f4f5; margin: 0 0 8px; }
        .attach-list { margin: 0; padding-left: 18px; font-size: 12px; color: #a1a1aa; line-height: 1.8; }
        .attach-list strong { color: #d4d4d8; }

        /* external events */
        .ext-card { background: #18181b; border: 1px solid #27272a; border-radius: 8px; padding: 20px; margin-bottom: 14px; }
        .ext-org { font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: #52525b; margin: 0 0 6px; }
        .ext-title { font-size: 15px; font-weight: bold; color: #4ade80; margin: 0 0 12px; line-height: 1.3; }
        .ext-meta { font-size: 12px; color: #d4d4d8; margin: 0 0 4px; }
        .ext-meta strong { color: #f4f4f5; }
        .ext-program { margin: 12px 0 14px; padding-left: 18px; font-size: 12px; color: #a1a1aa; line-height: 1.65; }
        .ext-note { font-size: 11px; color: #71717a; margin: 0 0 14px; }
        .ext-cta { display: inline-block; background: #27272a; border: 1px solid #4ade8060; color: #4ade80; font-weight: bold; font-size: 12px; text-decoration: none; padding: 10px 20px; border-radius: 5px; }

        /* next event */
        .next { text-align: center; font-size: 12px; color: #71717a; line-height: 1.8; margin: 32px 0 0; }
        .next strong { color: #4ade80; }

        /* footer */
        .footer { border-top: 1px solid #1c1c1f; margin-top: 32px; padding-top: 24px; font-size: 11px; color: #3f3f46; line-height: 1.8; text-align: center; }
        .footer a { color: #52525b; text-decoration: underline; }
    </style>
</head>
<body>
<div class="wrap">

    <p class="logo"><span>&gt;_</span> ŽďárAI</p>
    <p class="subtitle">AI události pro programátory a AI nadšence &middot; zdarai.cz</p>

    {{-- INTRO — liší se podle varianty --}}
    <div class="intro">
        @if($variant === \App\Mail\EventFollowUp::VARIANT_ATTENDED)
            <div class="intro-badge">Děkujeme</div>
            <div class="intro-title">Díky za <span>ŽďárAI #3</span></div>
            <p>{{ $recipientName ? 'Ahoj ' . $recipientName . ',' : 'Ahoj,' }}</p>
            <p>
                díky všem, kdo jste si ve čtvrtek udělali čas a přišli do IT NeŠkoly.
                Od obou přednášejících toho zaznělo hodně a debata po programu
                stála aspoň za tolik, co samotné přednášky.
            </p>
            <p>
                Honza Zbirovský byl tak hodný a poslal svoje slidy — najdete je
                <strong>v příloze tohoto mailu</strong>. A protože se v okolí chystají
                dvě akce, které by vás mohly zajímat, přidávám i pozvánku.
            </p>
        @elseif($variant === \App\Mail\EventFollowUp::VARIANT_SPEAKER)
            <div class="intro-badge">Děkujeme</div>
            <div class="intro-title">Díky za přednášku na <span>ŽďárAI #3</span></div>
            <p>{{ $recipientName ? 'Dobrý den ' . $recipientName . ',' : 'Dobrý den,' }}</p>
            <p>
                ještě jednou moc děkujeme {{ $talkTitle ? 'za přednášku „' . $talkTitle . '“' : 'za vaši přednášku' }}.
                Bez lidí, kteří si dají práci s přípravou tématu a postaví se před sál,
                by ŽďárAI nebylo. Ohlasy byly výborné a diskuze po programu se pěkně
                protáhla — což je vždycky ta nejlepší zpětná vazba.
            </p>
            <p>
                Účastníkům jsme rozeslali shrnutí večera a zmínili jsme i dvě akce
                v okolí, které se chystají. Posíláme je i vám — třeba se tam potkáme,
                tentokrát v publiku.
            </p>
        @else
            <div class="intro-badge">Ohlédnutí</div>
            <div class="intro-title">Co zaznělo na <span>ŽďárAI #3</span></div>
            <p>{{ $recipientName ? 'Ahoj ' . $recipientName . ',' : 'Ahoj,' }}</p>
            <p>
                ve čtvrtek 10. září proběhlo v IT NeŠkole třetí setkání ŽďárAI.
                Nedorazili jste, tak aspoň malé ohlédnutí — a hlavně materiály,
                které se hodí i bez toho, že jste u toho byli.
            </p>
            <p>
                Honza Zbirovský poslal svoje slidy, najdete je
                <strong>v příloze tohoto mailu</strong>. A v okolí se chystají dvě akce,
                na které stojí za to zajít.
            </p>
        @endif
    </div>

    {{-- PŘÍLOHY — jen když opravdu nějaké jsou --}}
    @if($attachmentPaths !== [])
    <div class="section-label">V příloze</div>
    <div class="attach">
        <p class="attach-title">📎 Prezentace Jana Zbirovského</p>
        <ul class="attach-list">
            <li><strong>Lokální jazykové modely a jejich využití v praxi</strong> — ŽďárAI #1, červen 2026</li>
            <li><strong>Lokální jazykové modely II: praktické ukázky</strong> — ŽďárAI #3, září 2026</li>
        </ul>
    </div>
    @endif

    {{-- REKAPITULACE PROGRAMU --}}
    <div class="section-label">Program večera</div>
    @foreach($event->talks->sortBy('sort_order') as $talk)
        <div class="talk-card">
            <p class="talk-speaker">{{ $talk->speaker->name }}</p>
            <p class="talk-name">{{ $talk->title }}</p>
            <p class="talk-desc">{{ $talk->description }}</p>
        </div>
    @endforeach

    {{-- AKCE V OKOLÍ --}}
    <div class="section-label">Kam zajít v okolí</div>
    @foreach($externalEvents as $ext)
        <div class="ext-card">
            <p class="ext-org">{{ $ext['organizer'] }}</p>
            <p class="ext-title">{{ $ext['title'] }}</p>
            <p class="ext-meta">📅 <strong>{{ $ext['date'] }}</strong>@if($ext['time']) od {{ $ext['time'] }}@endif</p>
            <p class="ext-meta">📍 {{ $ext['venue'] }}</p>
            <ul class="ext-program">
                @foreach($ext['program'] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
            <p class="ext-note">{{ $ext['note'] }}</p>
            <a class="ext-cta" href="{{ $ext['url'] }}">Registrovat se &rarr;</a>
        </div>
    @endforeach

    <p class="next">
        Pořádá je někdo jiný než my — jen nám přijde škoda o nich nevědět.<br>
        Další <strong>ŽďárAI</strong> chystáme a dáme vědět, jakmile bude program pohromadě.
    </p>

    {{-- FOOTER --}}
    <div class="footer">
        @if($variant === \App\Mail\EventFollowUp::VARIANT_SPEAKER)
            Píšeme vám jako přednášejícímu na ŽďárAI #3.<br>
        @else
            Tuto zprávu jste obdrželi jako odběratel novinek ŽďárAI.<br>
        @endif
        @if($unsubscribeUrl)
            <a href="{{ $unsubscribeUrl }}">Odhlásit odběr</a> &middot;
        @endif
        <a href="{{ url('/') }}">zdarai.cz</a> &middot; klara@zdarai.cz<br>
        IT NeŠkola &middot; Dolní 1, Žďár nad Sázavou
    </div>

</div>
</body>
</html>
