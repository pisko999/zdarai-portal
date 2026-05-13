<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Čekací listina</title>
    <style>
        body { font-family: monospace; background: #f9fafb; color: #111827; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; }
        .logo { color: #15803d; font-size: 22px; font-weight: bold; margin: 0 0 2px; }
        .subtitle { color: #6b7280; font-size: 13px; margin: 0 0 32px; }
        p { color: #374151; line-height: 1.7; margin: 0 0 16px; }
        .card { border: 1px solid #bbf7d0; border-radius: 8px; padding: 24px; margin: 24px 0; background: #f0fdf4; }
        .card-row { display: flex; gap: 8px; margin-bottom: 10px; font-size: 14px; }
        .card-label { color: #6b7280; min-width: 90px; }
        .card-value { color: #111827; font-weight: 500; }
        .waitlist-box { border: 1px solid #fde68a; border-radius: 8px; padding: 20px; margin: 24px 0; background: #fffbeb; }
        a { color: #16a34a; }
        .footer { margin-top: 48px; padding-top: 24px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #9ca3af; line-height: 1.8; }
    </style>
</head>
<body bgcolor="#f9fafb" style="font-family: monospace; background: #f9fafb; margin: 0; padding: 0;">
<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f9fafb" style="background:#f9fafb;">
<tr><td style="padding: 40px 20px;">
<div class="container">
    <p class="logo">&gt;_ ŽďárAI</p>
    <p class="subtitle">AI události pro vývojáře &middot; Hernice, Žďár nad Sázavou</p>

    <p>Dobrý den, <strong>{{ $registration->name }}</strong>,</p>
    <p>Děkujeme za zájem o akci. Bohužel je kapacita <strong>obsazena</strong> — vaše přihláška byla zařazena na čekací listinu.</p>

    <div class="waitlist-box">
        <p style="color: #92400e; font-weight: bold; margin: 0 0 8px;">⏳ Jste na čekací listině</p>
        <p style="margin: 0; font-size: 14px; color: #78350f;">Pokud se místo uvolní nebo kapacita navýší, budeme vás kontaktovat e-mailem.</p>
    </div>

    <div class="card">
        <div class="card-row">
            <span class="card-label">📅 Akce</span>
            <span class="card-value"><strong>{{ $registration->event->title }}</strong></span>
        </div>
        <div class="card-row">
            <span class="card-label">🗓️ Datum</span>
            <span class="card-value">{{ $registration->event->date->format('j. n. Y') }} v {{ $registration->event->date->format('H:i') }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">📍 Místo</span>
            <span class="card-value">{{ $registration->event->location ?? 'Hernice, Nádražní 1141/44, Žďár nad Sázavou' }}</span>
        </div>
    </div>

    <div class="footer">
        Tuto zprávu jste obdrželi, protože jste se zaregistrovali na akci.<br>
        Nechcete dostávat zprávy?
        <a href="{{ url('/opt-out/' . $registration->token) }}">Odhlásit se</a>
        &nbsp;&middot;&nbsp; ŽďárAI portál
    </div>
</div>
</td></tr>
</table>
</body>
</html>
