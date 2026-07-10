<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flyer — {{ $event->title }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700;800&display=swap');

        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page { size: A5 portrait; margin: 0; }

        html, body {
            width: 148mm;
            height: 210mm;
            background: #09090b;
            font-family: 'JetBrains Mono', monospace;
            color: #f4f4f5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .flyer {
            width: 148mm;
            height: 210mm;
            padding: 7mm 9mm 6mm;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            background: #09090b;
        }

        .flyer::before {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(
                0deg, transparent, transparent 2px,
                rgba(74, 222, 128, 0.015) 2px,
                rgba(74, 222, 128, 0.015) 4px
            );
            pointer-events: none;
            z-index: 0;
        }

        .flyer > * { position: relative; z-index: 1; }

        /* ── HEADER ── */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4mm;
        }

        .logo {
            font-size: 14pt;
            font-weight: 800;
            color: #4ade80;
            letter-spacing: -0.5px;
        }

        .logo span { color: #71717a; }

        .badge {
            font-size: 7pt;
            color: #09090b;
            background: #4ade80;
            padding: 2.5px 8px;
            border-radius: 3px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── TITLE ── */
        .title-block { margin-bottom: 3.5mm; text-align: center; }

        .event-num {
            font-size: 7.5pt;
            color: #a1a1aa;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 1.5mm;
        }

        .event-title {
            font-size: 20pt;
            font-weight: 800;
            color: #4ade80;
            line-height: 1.15;
            letter-spacing: -0.5px;
            white-space: nowrap;
        }

        /* ── META ── */
        .meta {
            display: flex;
            gap: 3mm;
            margin-bottom: 4mm;
            flex-wrap: wrap;
        }

        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 2mm;
            background: #18181b;
            border: 1px solid #52525b;
            border-radius: 4px;
            padding: 2.5mm 3.5mm;
            font-size: 8pt;
            color: #d4d4d8;
        }

        .meta-chip .icon { color: #4ade80; }
        .meta-chip strong { color: #f4f4f5; font-weight: 600; }

        /* ── DIVIDERS ── */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, #4ade80 0%, #27272a 60%);
            margin-bottom: 4mm;
        }

        .divider-plain {
            height: 1px;
            background: #3f3f46;
            margin-bottom: 3mm;
        }

        /* ── SPEAKERS ── */
        .speakers {
            display: flex;
            flex-direction: column;
            gap: 3mm;
            flex: 1;
            margin-bottom: 3mm;
        }

        .speaker-card {
            flex: 1;                /* každý blok bere 50 % dostupné výšky */
            display: flex;
            gap: 4mm;
            background: #18181b;
            border: 1px solid #52525b;
            border-radius: 8px;
            padding: 4mm;
        }

        .speaker-photo {
            width: 28mm;
            border-radius: 6px;
            object-fit: cover;
            object-position: top;
            border: 1px solid #4ade8050;
            flex-shrink: 0;
            align-self: stretch;
        }

        .speaker-photo-placeholder {
            width: 28mm;
            border-radius: 6px;
            background: #27272a;
            border: 1px solid #52525b;
            flex-shrink: 0;
            align-self: stretch;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10pt;
            color: #71717a;
        }

        .speaker-body {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 2mm;
        }

        .speaker-name {
            font-size: 11pt;
            font-weight: 700;
            color: #4ade80;
        }

        .talk-title {
            font-size: 9pt;
            font-weight: 600;
            color: #f4f4f5;
            line-height: 1.35;
        }

        .talk-desc {
            font-size: 7.5pt;
            color: #e4e4e7;
            line-height: 1.5;
        }

        /* ── FOOTER ── */
        .footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 3mm;
        }

        .footer-left { flex: 1; }

        .free-badge {
            display: inline-block;
            background: #4ade80;
            color: #09090b;
            font-size: 10.5pt;
            font-weight: 800;
            padding: 1.5mm 4mm;
            border-radius: 4px;
            letter-spacing: 0.5px;
            margin-bottom: 2mm;
        }

        .footer-meta {
            font-size: 7.5pt;
            color: #a1a1aa;
            line-height: 1.6;
        }

        .footer-meta strong { color: #f4f4f5; }

        .footer-url {
            font-size: 10pt;
            font-weight: 700;
            color: #4ade80;
            letter-spacing: -0.3px;
            margin-top: 1.5mm;
        }

        .qr-box {
            width: 24mm;
            height: 24mm;
            background: #f4f4f5;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            padding: 1.5mm;
        }

        .qr-box canvas, .qr-box img {
            width: 100% !important;
            height: 100% !important;
        }

        /* ── SCREEN BUTTONS ── */
        .print-btn, .pdf-btn {
            position: fixed;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-size: 12px;
            border-radius: 6px;
            cursor: pointer;
            z-index: 100;
            text-decoration: none;
            border: none;
            padding: 8px 18px;
        }
        .print-btn {
            top: 12px;
            right: 12px;
            background: #4ade80;
            color: #09090b;
        }
        .pdf-btn {
            top: 12px;
            right: 120px;
            background: #18181b;
            color: #4ade80;
            border: 1px solid #4ade80;
        }

        @media print {
            .print-btn, .pdf-btn { display: none; }
            html, body { width: 148mm; height: 210mm; }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">Tisknout</button>
<a class="pdf-btn" href="{{ route('flayer.pdf') }}">Stáhnout PDF</a>

<div class="flyer">

    {{-- HEADER --}}
    <div class="header">
        <div class="logo"><span>&gt;_</span> ŽďárAI</div>
        <div class="badge">Vstup zdarma</div>
    </div>

    {{-- TITLE --}}
    @php
        $parts = explode(' — ', $event->title, 2);
        $eventNum = $parts[0] ?? $event->title;
        $subtitle = $parts[1] ?? $event->title;
    @endphp
    <div class="title-block">
        <div class="event-num">{{ $eventNum }} &nbsp;/&nbsp; přednášky &amp; networking</div>
        <div class="event-title">{{ $subtitle }}</div>
    </div>

    {{-- META --}}
    <div class="meta">
        <div class="meta-chip">
            <span class="icon">&#128197;</span>
            <div>
                <strong>{{ $event->date->locale('cs')->translatedFormat('j. F Y') }}</strong>
                &nbsp;v&nbsp; {{ $event->date->format('H:i') }}
            </div>
        </div>
        <div class="meta-chip">
            <span class="icon">&#128205;</span>
            <div>
                <strong>IT NeŠkola</strong> &nbsp;·&nbsp; Dolní 1, Žďár n. S.
            </div>
        </div>
    </div>

    <div class="divider"></div>

    {{-- SPEAKERS — každý blok flex:1 → 50/50 --}}
    <div class="speakers">
        @foreach($event->talks as $talk)
        <div class="speaker-card">
            @if($talk->speaker->avatar)
                <img
                    class="speaker-photo"
                    src="{{ asset('storage/' . $talk->speaker->avatar) }}"
                    alt="{{ $talk->speaker->name }}"
                >
            @else
                <div class="speaker-photo-placeholder">?</div>
            @endif
            <div class="speaker-body">
                <div class="speaker-name">{{ $talk->speaker->name }}</div>
                <div class="talk-title">{{ $talk->title }}</div>
                <div class="talk-desc">{{ $talk->description }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="divider-plain"></div>

    {{-- FOOTER --}}
    <div class="footer">
        <div class="footer-left">
            <div class="free-badge">VSTUP ZDARMA</div>
            <div class="footer-meta">
                Kapacita <strong>{{ $event->capacity }} míst</strong> &nbsp;·&nbsp; registrace nutná<br>
                Žďár nad Sázavou &nbsp;·&nbsp; většinou první čtvrtek v měsíci
            </div>
            <div class="footer-url">zdarai.cz</div>
        </div>
        <div class="qr-box" id="qr"></div>
    </div>

</div>

<script>
    new QRCode(document.getElementById('qr'), {
        text: 'https://zdarai.cz',
        width: 90,
        height: 90,
        colorDark: '#000000',
        colorLight: '#f4f4f5',
        correctLevel: QRCode.CorrectLevel.M,
    });
</script>

</body>
</html>
