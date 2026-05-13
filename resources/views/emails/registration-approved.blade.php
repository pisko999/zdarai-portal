<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Registrace potvrzena</title>
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
        .cta { display: inline-block; background: #16a34a; color: #ffffff !important; font-weight: bold; text-decoration: none; padding: 14px 28px; border-radius: 6px; margin: 20px 0; font-size: 14px; }
        a { color: #16a34a; }
        .footer { margin-top: 48px; padding-top: 24px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #9ca3af; line-height: 1.8; }
        .badge { display: inline-block; background: #16a34a; color: #ffffff; font-size: 11px; font-weight: bold; padding: 2px 8px; border-radius: 4px; }
        .info-box { border-left: 3px solid #16a34a; background: #f0fdf4; padding: 12px 16px; margin: 16px 0; border-radius: 0 6px 6px 0; font-size: 14px; }
    </style>
</head>
<body bgcolor="#f9fafb" style="font-family: monospace; background: #f9fafb; margin: 0; padding: 0;">
<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f9fafb" style="background:#f9fafb;">
<tr><td style="padding: 40px 20px;">
<div class="container">
    <p class="logo">&gt;_ ŽďárAI</p>
    <p class="subtitle">AI události pro vývojáře &middot; Hernice, Žďár nad Sázavou</p>

    <p>Dobrý den, <strong>{{ $registration->name }}</strong>,</p>
    <p>Vaše registrace byla <strong>potvrzena</strong>. Těšíme se na vás! 🎉</p>

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
        <div class="card-row">
            <span class="card-label">💰 Vstupné</span>
            @if($registration->event->price)
            <span class="card-value">{{ number_format($registration->event->price, 0) }} Kč</span>
            @else
            <span class="card-value"><span class="badge">ZDARMA</span></span>
            @endif
        </div>
    </div>

    <a href="{{ url('/udalosti/' . $registration->event->slug) }}" class="cta">Zobrazit detail akce &rarr;</a>

    @if($setPasswordUrl)
    <div class="info-box">
        <p style="margin: 0 0 8px; color: #15803d; font-weight: bold;">🔐 Přihlaste se a sledujte své registrace</p>
        <p style="margin: 0 0 12px; font-size: 13px; color: #374151;">Nastavte si heslo jedním kliknutím — odkaz je platný 24 hodin.</p>
        <a href="{{ $setPasswordUrl }}" style="display: inline-block; background: #15803d; color: #ffffff !important; font-weight: bold; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-size: 13px;">Nastavit heslo &rarr;</a>
    </div>
    @endif

    <p>Připomínku vám pošleme 3 dny před akcí.</p>

    <div class="footer">
        Tuto zprávu jste obdrželi, protože jste se zaregistrovali na akci.<br>
        Nechcete dostávat připomínky?
        <a href="{{ url('/opt-out/' . $registration->token) }}">Odhlásit se</a>
        &nbsp;&middot;&nbsp; ŽďárAI portál
    </div>
</div>
</td></tr>
</table>
</body>
</html>
