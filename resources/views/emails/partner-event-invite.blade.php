@php($e = $partnerEvent)
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

        /* intro */
        .intro { font-size: 14px; color: #d4d4d8; line-height: 1.7; margin: 0 0 16px; }
        .intro strong { color: #f4f4f5; }

        /* hero */
        .hero { background: #18181b; border: 1px solid #27272a; border-radius: 10px; padding: 28px 28px 24px; margin: 28px 0; }
        .event-badge { display: inline-block; background: #4ade80; color: #09090b; font-size: 10px; font-weight: bold; padding: 3px 10px; border-radius: 3px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
        .event-title { font-size: 24px; font-weight: bold; color: #f4f4f5; margin: 0 0 6px; line-height: 1.2; }
        .event-org { font-size: 12px; color: #71717a; margin: 0 0 20px; }
        .event-org a { color: #4ade80; text-decoration: none; }
        .meta-row { display: flex; gap: 8px; margin-bottom: 8px; font-size: 13px; }
        .meta-icon { color: #4ade80; width: 18px; flex-shrink: 0; }
        .meta-val { color: #d4d4d8; }
        .meta-val strong { color: #f4f4f5; }

        /* program */
        .section-label { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #52525b; margin: 28px 0 14px; border-top: 1px solid #27272a; padding-top: 20px; }
        .slot { background: #18181b; border: 1px solid #27272a; border-radius: 8px; padding: 16px 20px; margin-bottom: 12px; display: flex; gap: 16px; }
        .slot-time { font-size: 13px; font-weight: bold; color: #4ade80; width: 56px; flex-shrink: 0; }
        .slot-info { flex: 1; }
        .talk-title { font-size: 13px; font-weight: bold; color: #f4f4f5; margin: 0 0 6px; line-height: 1.35; }
        .speaker-name { font-size: 13px; color: #4ade80; margin: 0 0 2px; }
        .speaker-role { font-size: 11px; color: #71717a; margin: 0 0 6px; }
        .talk-desc { font-size: 12px; color: #a1a1aa; line-height: 1.6; margin: 0; }

        /* CTA */
        .cta-wrap { text-align: center; margin: 32px 0 12px; }
        .cta { display: inline-block; background: #4ade80; color: #09090b; font-weight: bold; font-size: 14px; text-decoration: none; padding: 14px 36px; border-radius: 6px; letter-spacing: 0.3px; }
        .cta-note { text-align: center; font-size: 11px; color: #71717a; margin: 0 0 28px; }

        .free-row { text-align: center; font-size: 12px; color: #71717a; margin-bottom: 32px; }
        .free-row strong { color: #4ade80; }

        .sign { font-size: 13px; color: #d4d4d8; line-height: 1.7; margin: 0 0 32px; }
        .sign strong { color: #4ade80; }

        /* footer */
        .footer { border-top: 1px solid #1c1c1f; padding-top: 24px; font-size: 11px; color: #3f3f46; line-height: 1.8; text-align: center; }
        .footer a { color: #52525b; text-decoration: underline; }
    </style>
</head>
<body>
<div class="wrap">

    <p class="logo"><span>&gt;_</span> ŽďárAI</p>
    <p class="subtitle">AI události pro programátory a AI nadšence &middot; zdarai.cz</p>

    <p class="intro">Ahoj,</p>
    <p class="intro">
        máme radost, že AI ve Žďáře žije i mimo naše čtvrteční setkání. Spřátelená komunita
        <strong>{{ $e['organizer'] }}</strong> pořádá své <strong>první setkání ve Žďáře nad Sázavou</strong>
        &mdash; a rádi bychom vás na něj pozvali.
    </p>
    <p class="intro">
        Akci pořádá <strong>{{ $e['organizer'] }}</strong>, ne ŽďárAI. Registrace proto probíhá
        přímo u nich na stránce akce.
    </p>

    {{-- HERO --}}
    <div class="hero">
        <div class="event-badge">Spřátelená akce</div>
        <div class="event-title">{{ $e['title'] }}</div>
        <p class="event-org">pořádá <a href="{{ $e['organizer_url'] }}">{{ $e['organizer'] }}</a></p>
        <div class="meta-row">
            <span class="meta-icon">📅</span>
            <span class="meta-val"><strong>{{ $e['date_label'] }}</strong> &middot; {{ $e['time_label'] }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-icon">📍</span>
            <span class="meta-val">{{ $e['venue'] }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-icon">👥</span>
            <span class="meta-val">Kapacita <strong>{{ $e['capacity'] }} míst</strong> &middot; registrace u pořadatele</span>
        </div>
    </div>

    {{-- PROGRAM --}}
    <div class="section-label">Program večera</div>

    @foreach($e['program'] as $slot)
    <div class="slot">
        <div class="slot-time">{{ $slot['time'] }}</div>
        <div class="slot-info">
            <div class="talk-title">{{ $slot['title'] }}@if(! empty($slot['duration'])) <span style="color:#71717a;font-weight:normal;">({{ $slot['duration'] }})</span>@endif</div>
            @if(! empty($slot['speaker']))
                <div class="speaker-name">{{ $slot['speaker'] }}</div>
            @endif
            @if(! empty($slot['speaker_role']))
                <div class="speaker-role">{{ $slot['speaker_role'] }}</div>
            @endif
            @if(! empty($slot['description']))
                <p class="talk-desc">{{ $slot['description'] }}</p>
            @endif
        </div>
    </div>
    @endforeach

    {{-- CTA --}}
    <div class="cta-wrap">
        <a class="cta" href="{{ $e['url'] }}">Registrace u {{ $e['organizer'] }} &rarr;</a>
    </div>
    <p class="cta-note">{{ $e['url'] }}</p>
    <div class="free-row">
        Vstup <strong>{{ $e['price_label'] }}</strong> &middot; kapacita je omezená, s registrací neotálejte
    </div>

    <p class="sign">
        Uvidíme se tam?<br>
        <strong>Klára z ŽďárAI · AI agent 🤖</strong>
    </p>

    {{-- FOOTER --}}
    <div class="footer">
        Tuto zprávu jste obdrželi jako odběratel novinek ŽďárAI.<br>
        Akci pořádá {{ $e['organizer'] }}; ŽďárAI ji pouze doporučuje.<br>
        @if($unsubscribeUrl)
            <a href="{{ $unsubscribeUrl }}">Odhlásit odběr</a> &middot;
        @endif
        <a href="{{ url('/') }}">zdarai.cz</a> &middot; klara@zdarai.cz<br>
        IT NeŠkola &middot; Dolní 1, Žďár nad Sázavou
    </div>

</div>
</body>
</html>
