<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Posezení po {{ $registration->event->title }}</title>
    <style>
        body { font-family: monospace; background: #f9fafb; color: #111827; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; }
        .logo { color: #15803d; font-size: 22px; font-weight: bold; margin: 0 0 2px; }
        .subtitle { color: #6b7280; font-size: 13px; margin: 0 0 32px; }
        p { color: #374151; line-height: 1.7; margin: 0 0 16px; }
        .card { border: 1px solid #bbf7d0; border-radius: 8px; padding: 24px; margin: 24px 0; background: #f0fdf4; }
        .cta { display: inline-block; background: #16a34a; color: #ffffff !important; font-weight: bold; text-decoration: none; padding: 14px 28px; border-radius: 6px; margin: 8px 0 20px; font-size: 14px; }
        a { color: #16a34a; }
        .footer { margin-top: 48px; padding-top: 24px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #9ca3af; line-height: 1.8; }
        .info-box { border-left: 3px solid #16a34a; background: #f0fdf4; padding: 12px 16px; margin: 16px 0; border-radius: 0 6px 6px 0; font-size: 14px; }
        .menu { margin: 0 0 16px; padding: 0 0 0 20px; }
        .menu li { color: #374151; font-size: 14px; line-height: 1.6; margin-bottom: 10px; }
        h2 { color: #15803d; font-size: 16px; margin: 32px 0 8px; }
    </style>
</head>
<body bgcolor="#f9fafb" style="font-family: monospace; background: #f9fafb; margin: 0; padding: 0;">
<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f9fafb" style="background:#f9fafb;">
<tr><td style="padding: 40px 20px;">
<div class="container">
    <p class="logo">&gt;_ ŽďárAI</p>
    <p class="subtitle">AI události pro programátory a AI nadšence &middot; IT NeŠkola, Žďár nad Sázavou</p>

    <p>Ahoj, <strong>{{ $registration->name }}</strong>,</p>

    <p>po skončení programu <strong>{{ $registration->event->title }}</strong> ({{ $registration->event->date->translatedFormat('l j. n.') }})
       se přesuneme na neformální posezení do <strong>Restaurace Krystal, Dolní 183/30, Žďár nad Sázavou</strong>
       — kousek od IT NeŠkoly. Účast je dobrovolná a každý si platí sám.</p>

    <p>Restaurace potřebuje znát počty jídel předem, proto nám prosím dej vědět v krátké anketě,
       jestli dorazíš na večeři, jen na pivo, nebo vůbec:</p>

    <a href="{{ $pollUrl }}" class="cta">Vyplnit anketu &rarr;</a>

    <h2>🍽️ Z čeho můžeš vybírat</h2>
    <ul class="menu">
        @foreach($foodOptions as $option)
        <li>{{ $option }}</li>
        @endforeach
    </ul>
    <p style="font-size: 13px; color: #6b7280;">Nechce se ti nic z nabídky? V anketě jde zvolit i
       klasika (řízek, smažák…) — stačí ji upřesnit v poznámce. Ceny jsou informativní, každý si platí sám.</p>

    <div class="info-box">
        ⏰ <strong>Odpověz prosím do středy 8. 7. 20:00</strong>, ať můžeme restauraci nahlásit počty jídel.
    </div>

    <p style="margin-top: 32px;">Těšíme se na vás!</p>
    <p style="margin: 0 0 4px;"><strong>Klára z ŽďárAI</strong> &middot; AI agent 🤖</p>
    <p style="margin: 0; font-size: 12px; color: #6b7280;">Reply-to: klara@zdarai.cz &middot; Organizační tým ŽďárAI</p>

    <div class="footer">
        Tuto zprávu jste obdrželi, protože jste se zaregistrovali na akci ŽďárAI.<br>
        Nechcete dostávat další zprávy?
        <a href="{{ $optOutUrl }}">Odhlásit se</a>
        &nbsp;&middot;&nbsp; ŽďárAI portál
    </div>
</div>
</td></tr>
</table>
</body>
</html>
