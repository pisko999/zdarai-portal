@php($e = $partnerEvent)
@php($aud = $audience ?? 'subscriber')
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $e['title'] }}</title>
    <style>
        body { font-family: monospace; background: #09090b; color: #f4f4f5; margin: 0; padding: 0; }
        .wrap { max-width: 600px; margin: 0 auto; padding: 40px 20px; }

        /* header */
        .logo { font-size: 22px; font-weight: bold; color: #4ade80; margin: 0 0 2px; }
        .logo span { color: #52525b; }
        .subtitle { color: #71717a; font-size: 12px; margin: 0 0 36px; }

        /* text */
        .intro { font-size: 14px; color: #d4d4d8; line-height: 1.7; margin: 0 0 16px; }
        .intro strong { color: #f4f4f5; }
        .intro a { color: #4ade80; text-decoration: none; }

        /* hero */
        .hero { background: #18181b; border: 1px solid #27272a; border-radius: 10px; padding: 28px 28px 24px; margin: 28px 0; }
        .event-badge { display: inline-block; background: #4ade80; color: #09090b; font-size: 10px; font-weight: bold; padding: 3px 10px; border-radius: 3px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
        .event-title { font-size: 24px; font-weight: bold; color: #f4f4f5; margin: 0 0 6px; line-height: 1.2; }
        .event-org { font-size: 12px; color: #71717a; margin: 0 0 20px; }
        .event-org a { color: #4ade80; text-decoration: none; }
        .meta-row { display: flex; gap: 8px; margin-bottom: 8px; font-size: 13px; line-height: 1.55; }
        .meta-icon { color: #4ade80; width: 22px; flex-shrink: 0; }
        .meta-val { color: #d4d4d8; }
        .meta-val strong { color: #f4f4f5; }

        /* sections */
        .section-label { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #52525b; margin: 28px 0 14px; border-top: 1px solid #27272a; padding-top: 20px; }
        .guest { background: #18181b; border: 1px solid #27272a; border-radius: 8px; padding: 12px 18px; margin-bottom: 8px; }
        .guest-name { font-size: 14px; font-weight: bold; color: #4ade80; margin: 0 0 2px; }
        .guest-role { font-size: 12px; color: #a1a1aa; margin: 0; }
        .steps { font-size: 14px; color: #d4d4d8; line-height: 1.7; margin: 0 0 16px; padding-left: 22px; }
        .steps li { margin-bottom: 6px; }

        /* CTA */
        .cta-wrap { text-align: center; margin: 32px 0 12px; }
        .cta { display: inline-block; background: #4ade80; color: #09090b; font-weight: bold; font-size: 14px; text-decoration: none; padding: 14px 36px; border-radius: 6px; letter-spacing: 0.3px; }
        .cta-note { text-align: center; font-size: 11px; color: #71717a; margin: 0 0 28px; }

        .sign { font-size: 13px; color: #d4d4d8; line-height: 1.7; margin: 0 0 32px; }
        .sign strong { color: #4ade80; }

        /* ŽďárAI box */
        .promo { background: #052e16; border: 1px solid #166534; border-radius: 10px; padding: 24px 24px 20px; margin: 8px 0 32px; }
        .promo-label { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #86efac; margin: 0 0 10px; }
        .promo-title { font-size: 18px; font-weight: bold; color: #f4f4f5; margin: 0 0 10px; line-height: 1.3; }
        .promo-text { font-size: 13px; color: #d4d4d8; line-height: 1.7; margin: 0 0 14px; }
        .promo-talk { font-size: 13px; color: #d4d4d8; line-height: 1.5; margin: 0 0 8px; }
        .promo-talk strong { color: #4ade80; }
        .promo-cta { display: inline-block; background: #4ade80; color: #09090b; font-weight: bold; font-size: 13px; text-decoration: none; padding: 10px 24px; border-radius: 6px; margin-top: 8px; }
        .promo-text a { color: #4ade80; text-decoration: underline; }

        /* opt-in do newsletteru (jen audience list) — sekundární vůči hlavnímu CTA */
        .optin { background: #18181b; border: 1px dashed #4ade80; border-radius: 10px; padding: 22px 24px 20px; margin: 0 0 32px; text-align: center; }
        .optin-title { font-size: 15px; font-weight: bold; color: #f4f4f5; margin: 0 0 8px; }
        .optin-text { font-size: 13px; color: #d4d4d8; line-height: 1.7; margin: 0 0 16px; }
        .optin-cta { display: inline-block; background: transparent; color: #4ade80; border: 1px solid #4ade80; font-weight: bold; font-size: 13px; text-decoration: none; padding: 10px 24px; border-radius: 6px; }

        /* footer */
        .footer { border-top: 1px solid #1c1c1f; padding-top: 24px; font-size: 11px; color: #3f3f46; line-height: 1.8; text-align: center; }
        .footer a { color: #52525b; text-decoration: underline; }
    </style>
</head>
{{-- Gmail zahazuje styly na <body> → pozadí i barvu textu i inline + bgcolor na obalové tabulce. --}}
<body bgcolor="#09090b" style="font-family: monospace; background: #09090b; color: #f4f4f5; margin: 0; padding: 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#09090b" style="background: #09090b;">
<tr><td bgcolor="#09090b" style="background: #09090b;">
<div class="wrap" style="max-width: 600px; margin: 0 auto; padding: 40px 20px; background: #09090b; color: #f4f4f5;">

    <p class="logo"><span>&gt;_</span> ŽďárAI</p>
    <p class="subtitle">AI události pro programátory a AI nadšence &middot; zdarai.cz</p>

    {{-- ÚVOD podle segmentu --}}
    <p class="intro">Ahoj,</p>
    @if($aud === 'list')
        <p class="intro">
            píšeme ti, protože ses přihlásil/a na první AI Monday ve Žďáru nad Sázavou. V listopadu se
            AI Monday ve Žďáru nekoná &ndash; ale Jihlava je kousek, tak tě rádi uvidíme u sousedů.
            Tohle setkání je navíc výjimečné.
        </p>
    @elseif($aud === 'registrant')
        <p class="intro">
            byl/a jsi na některé z akcí ŽďárAI, tak ti posíláme pozvánku od sousedů z AI Monday Jihlava.
            Jihlava je kousek a tohle setkání je navíc výjimečné.
        </p>
    @else
        <p class="intro">
            posíláme ti pozvánku od sousedů z AI Monday Jihlava. Jihlava je kousek a tohle setkání
            je navíc výjimečné.
        </p>
    @endif

    <p class="intro">
        V Jihlavě slavíme první rok AI Monday a místo obvyklého pondělního večera v restauraci se
        zapojujeme do větší akce pro širokou veřejnost &ndash; <strong>{{ $e['title'] }}</strong>, která je
        součástí největšího AI festivalu v Česku, <a href="{{ $e['festival_url'] }}">{{ $e['festival'] }}</a>.
    </p>

    {{-- HERO --}}
    <div class="hero">
        <div class="event-badge">Spřátelená akce</div>
        <div class="event-title">{{ $e['title'] }}</div>
        <p class="event-org">pořádá <a href="{{ $e['organizer_url'] }}">{{ $e['organizer'] }}</a> &middot; součást festivalu <a href="{{ $e['festival_url'] }}">{{ $e['festival'] }}</a></p>
        <div class="meta-row">
            <span class="meta-icon">📅</span>
            <span class="meta-val"><strong>Čtvrtek 5. 11. 2026</strong> (ne pondělí!)</span>
        </div>
        <div class="meta-row">
            <span class="meta-icon">🕕</span>
            <span class="meta-val"><strong>AI Monday od 18:00</strong> (do 19:30) &ndash; festivalový program běží už od 16:30</span>
        </div>
        <div class="meta-row">
            <span class="meta-icon">📍</span>
            <span class="meta-val">{{ $e['venue'] }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-icon">🎟️</span>
            <span class="meta-val"><strong>Zdarma</strong>, registrace není nutná &ndash; stačí přijít</span>
        </div>
    </div>

    {{-- HOSTÉ --}}
    <div class="section-label">5 hostů, 5 pohledů</div>
    <p class="intro">
        Do panelové diskuse jsme pozvali pět lidí, kteří AI používají každý den &ndash; každý úplně jinak:
    </p>
    @foreach($e['guests'] ?? [] as $guest)
    <div class="guest">
        <div class="guest-name">{{ $guest['name'] }}</div>
        <p class="guest-role">{{ $guest['role'] }}</p>
    </div>
    @endforeach

    {{-- PRŮBĚH --}}
    <div class="section-label">Jak to bude probíhat</div>
    <ol class="steps">
        <li>Krátce připomeneme, co jsme za rok AI Monday v Jihlavě zažili.</li>
        <li>Kolečko hostů: každý za sebe řekne, jak AI používá v práci i v osobním životě a jak se to za poslední rok změnilo.</li>
        <li>Pak prostor pro otázky &ndash; od moderátorů i z publika.</li>
    </ol>
    <p class="intro">
        Žádná přednáška z pódia, chceme uvolněnou debatu. Přijď se zapojit a vezmi s sebou otázky,
        které tě pálí.
    </p>
    <p class="intro">
        Pokud máš čas dřív, zastav se už na odpolední festivalový program od 16:30. Víc o celé akci
        najdeš na <a href="{{ $e['organizer_url'] }}">aimonday.cz</a> a na <a href="{{ $e['festival_url'] }}">dny.ai</a>.
    </p>

    {{-- CTA --}}
    <div class="cta-wrap">
        <a class="cta" href="{{ $e['url'] }}">Program AI for All Jihlava &rarr;</a>
    </div>
    <p class="cta-note">{{ $e['url'] }}</p>

    <p class="intro">
        Klidně pozvánku přepošli dál &ndash; akce je pro širokou veřejnost, takže vezmi kolegy, kamarády
        i ty, kdo s AI teprve začínají.
    </p>

    <p class="sign">
        Těšíme se ve čtvrtek!<br>
        <strong>{{ $e['organizer'] }}</strong>
    </p>

    {{-- BOX ŽďárAI — jen pokud je akce published --}}
    @if(! empty($promoEvent))
    <div class="promo" style="background: #052e16; border: 1px solid #166534;">
        <p class="promo-label">A týden nato ve Žďáru</p>
        <div class="promo-title">{{ $promoEvent->title }}</div>
        <p class="promo-text">
            Ve Žďáru se v listopadu potkáme o týden později na ŽďárAI #4 &ndash; i to je letos
            @if(! empty($e['promo_event_dnyai_url']))<a href="{{ $e['promo_event_dnyai_url'] }}" style="color:#4ade80;">součástí festivalu Dny AI</a>.@else součástí festivalu Dny AI.@endif
            Vstup zdarma, jen se prosím zaregistruj, ať víme, kolik nás bude.
        </p>
        <p class="promo-text" style="margin-bottom: 10px;">
            📅 <strong style="color:#f4f4f5;">{{ $promoEvent->date->locale('cs')->translatedFormat('l j. F Y \v H:i') }}</strong><br>
            📍 {{ $promoEvent->location }}
        </p>
        @foreach($promoEvent->talks->sortBy('sort_order') as $talk)
            <p class="promo-talk">
                &rarr; {{ $talk->title }}@if($talk->speaker) &middot; <strong>{{ $talk->speaker->name }}</strong>@endif
            </p>
        @endforeach
        <a class="promo-cta" href="{{ route('events.show', $promoEvent->slug) }}">Registrace na ŽďárAI #4 &rarr;</a>
    </div>
    @endif

    {{-- OPT-IN do newsletteru — jen audience list (bez souhlasu je nepřidáváme) --}}
    @if($aud === 'list' && ! empty($optinUrl))
    <div class="optin" style="background: #18181b; border: 1px dashed #4ade80;">
        <div class="optin-title">Chceš vědět o dalších akcích ve Žďáru?</div>
        <p class="optin-text">
            Pozvánky na ŽďárAI (zhruba jednou měsíčně) ti rádi pošleme &ndash; jen když o ně stojíš.
        </p>
        <a class="optin-cta" href="{{ $optinUrl }}" style="color: #4ade80; border: 1px solid #4ade80;">Chci dostávat novinky ŽďárAI &rarr;</a>
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <span style="color:#71717a;">Klára z ŽďárAI · AI agent 🤖</span><br>
        @if($aud === 'list')
            Tenhle e-mail ti posíláme jednorázově ve spolupráci s pořadateli AI Monday, protože ses
            registroval/a na AI Monday Žďár n. S. #1. Do newsletteru ŽďárAI tě bez tvého souhlasu nepřidáváme.
            Nechceš podobné tipy? Stačí odpovědět.<br>
        @elseif($aud === 'registrant')
            Tuto zprávu jste obdrželi jako účastník některé z akcí ŽďárAI.<br>
        @else
            Tuto zprávu jste obdrželi jako odběratel novinek ŽďárAI.<br>
        @endif
        Akci pořádá {{ $e['organizer'] }}; ŽďárAI ji pouze doporučuje.<br>
        @if($aud !== 'list' && $unsubscribeUrl)
            <a href="{{ $unsubscribeUrl }}">{{ $aud === 'registrant' ? 'Nechci dostávat další e-maily' : 'Odhlásit odběr' }}</a> &middot;
        @endif
        <a href="{{ url('/') }}">zdarai.cz</a> &middot; klara@zdarai.cz<br>
        IT NeŠkola &middot; Dolní 1, Žďár nad Sázavou
    </div>

</div>
</td></tr>
</table>
</body>
</html>
