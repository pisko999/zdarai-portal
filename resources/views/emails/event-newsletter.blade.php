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

        /* hero */
        .hero { background: #18181b; border: 1px solid #27272a; border-radius: 10px; padding: 28px 28px 24px; margin-bottom: 28px; }
        .event-badge { display: inline-block; background: #4ade80; color: #09090b; font-size: 10px; font-weight: bold; padding: 3px 10px; border-radius: 3px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
        .event-title { font-size: 24px; font-weight: bold; color: #f4f4f5; margin: 0 0 20px; line-height: 1.2; }
        .event-title span { color: #4ade80; }
        .meta-row { display: flex; gap: 8px; margin-bottom: 8px; font-size: 13px; }
        .meta-icon { color: #4ade80; width: 18px; flex-shrink: 0; }
        .meta-val { color: #d4d4d8; }
        .meta-val strong { color: #f4f4f5; }

        /* speakers */
        .section-label { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #52525b; margin: 28px 0 14px; border-top: 1px solid #27272a; padding-top: 20px; }
        .speaker-card { background: #18181b; border: 1px solid #27272a; border-radius: 8px; padding: 18px 20px; margin-bottom: 12px; display: flex; gap: 16px; }
        .speaker-photo { width: 64px; height: 64px; border-radius: 6px; object-fit: cover; object-position: top; border: 1px solid #4ade8040; flex-shrink: 0; }
        .speaker-info { flex: 1; }
        .speaker-name { font-size: 15px; font-weight: bold; color: #4ade80; margin: 0 0 4px; }
        .talk-title { font-size: 13px; font-weight: bold; color: #f4f4f5; margin: 0 0 8px; line-height: 1.35; }
        .talk-desc { font-size: 12px; color: #a1a1aa; line-height: 1.6; margin: 0; }

        /* CTA */
        .cta-wrap { text-align: center; margin: 32px 0 28px; }
        .cta { display: inline-block; background: #4ade80; color: #09090b; font-weight: bold; font-size: 14px; text-decoration: none; padding: 14px 36px; border-radius: 6px; letter-spacing: 0.3px; }

        /* free badge */
        .free-row { text-align: center; font-size: 12px; color: #71717a; margin-bottom: 32px; }
        .free-row strong { color: #4ade80; }

        /* footer */
        .footer { border-top: 1px solid #1c1c1f; padding-top: 24px; font-size: 11px; color: #3f3f46; line-height: 1.8; text-align: center; }
        .footer a { color: #52525b; text-decoration: underline; }
    </style>
</head>
<body>
<div class="wrap">

    <p class="logo"><span>&gt;_</span> ŽďárAI</p>
    <p class="subtitle">AI události pro programátory a AI nadšence &middot; zdarai.cz</p>

    {{-- HERO --}}
    <div class="hero">
        <div class="event-badge">Nadcházející akce</div>
        <div class="event-title">
            {{ explode(' — ', $event->title)[0] }} &mdash;<br>
            <span>{{ explode(' — ', $event->title)[1] ?? '' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-icon">📅</span>
            <span class="meta-val"><strong>{{ $event->date->locale('cs')->translatedFormat('j. F Y') }}</strong> v {{ $event->date->format('H:i') }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-icon">📍</span>
            <span class="meta-val"><strong>IT NeŠkola</strong> &mdash; Dolní 1, Žďár nad Sázavou</span>
        </div>
        <div class="meta-row">
            <span class="meta-icon">👥</span>
            <span class="meta-val">Kapacita <strong>{{ $event->capacity }} míst</strong> &middot; registrace nutná</span>
        </div>
    </div>

    {{-- SPEAKERS --}}
    <div class="section-label">Program večera</div>

    @foreach($event->talks->sortBy('sort_order') as $talk)
    <div class="speaker-card">
        @if($talk->speaker->avatar)
            <img class="speaker-photo"
                 src="{{ url('storage/' . $talk->speaker->avatar) }}"
                 alt="{{ $talk->speaker->name }}">
        @endif
        <div class="speaker-info">
            <div class="speaker-name">{{ $talk->speaker->name }}</div>
            <div class="talk-title">{{ $talk->title }}</div>
            <p class="talk-desc">{{ $talk->description }}</p>
        </div>
    </div>
    @endforeach

    {{-- CTA --}}
    <div class="cta-wrap">
        <a class="cta" href="{{ url('/udalosti/' . $event->slug) }}">Registrovat se &rarr;</a>
    </div>
    <div class="free-row">
        Vstup <strong>zdarma</strong> &middot; přijďte se inspirovat, sdílet zkušenosti a popovídat
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Tuto zprávu jste obdrželi jako odběratel novinek ŽďárAI.<br>
        @if($unsubscribeUrl)
            <a href="{{ $unsubscribeUrl }}">Odhlásit odběr</a> &middot;
        @endif
        <a href="{{ url('/') }}">zdarai.cz</a> &middot; klara@zdarai.cz<br>
        IT NeŠkola &middot; Dolní 1, Žďár nad Sázavou
    </div>

</div>
</body>
</html>
