<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Potvrzení registrace</title>
    <style>
        body { font-family: monospace; background: #0a0a0f; color: #86efac; margin: 0; padding: 40px 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        h1 { color: #4ade80; font-size: 24px; margin-bottom: 4px; }
        .subtitle { color: #166534; font-size: 13px; margin-bottom: 32px; }
        p { color: #86efac; line-height: 1.7; }
        .card { border: 1px solid #14532d; border-radius: 8px; padding: 24px; margin: 24px 0; background: #0f1a0f; }
        .card-row { display: flex; gap: 8px; margin-bottom: 8px; font-size: 14px; }
        .card-label { color: #166534; min-width: 90px; }
        .card-value { color: #86efac; }
        .cta { display: inline-block; background: #16a34a; color: #0a0a0f !important; font-weight: bold; text-decoration: none; padding: 14px 28px; border-radius: 6px; margin: 20px 0; font-size: 14px; }
        a { color: #4ade80; }
        .footer { margin-top: 48px; padding-top: 24px; border-top: 1px solid #14532d; font-size: 11px; color: #166534; line-height: 1.8; }
        .badge { display: inline-block; background: #14532d; color: #4ade80; font-size: 11px; font-weight: bold; padding: 2px 8px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h1>&gt;_ ŽďárAI</h1>
    <p class="subtitle">AI události pro vývojáře · Hernice, Žďár nad Sázavou</p>

    <p>Dobrý den, <strong>{{ $registration->name }}</strong>,</p>
    <p>Vaše registrace na akci byla úspěšně potvrzena. 🎉</p>

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

    <a href="{{ url('/udalosti/' . $registration->event->slug) }}" class="cta">Zobrazit detail akce →</a>

    @if($setPasswordUrl)
    <div style="border: 1px solid #14532d; border-radius: 8px; padding: 20px; margin: 24px 0; background: #0f1a0f;">
        <p style="margin: 0 0 12px; color: #4ade80; font-size: 14px; font-weight: bold;">🔐 Nastavte si heslo a sledujte své registrace</p>
        <p style="margin: 0 0 16px; color: #86efac; font-size: 13px;">Pro přihlášení a přehled registrací si nastavte heslo jedním kliknutím.</p>
        <a href="{{ $setPasswordUrl }}" style="display: inline-block; background: #166534; color: #4ade80 !important; font-weight: bold; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-size: 13px; border: 1px solid #16a34a;">Nastavit heslo →</a>
        <p style="margin: 12px 0 0; color: #166534; font-size: 11px;">Odkaz je platný 60 minut. Pokud heslo nenastavíte, nic se neděje — registrace je platná.</p>
    </div>
    @endif

    <p>Budeme rádi, pokud na akci přijdete! Připomínku vám pošleme 3 dny před akcí.</p>

    <div class="footer">
        Tuto zprávu jste obdrželi, protože jste se zaregistrovali na akci.<br>
        Nechcete dostávat připomínky?
        <a href="{{ url('/opt-out/' . $registration->token) }}">Odhlásit se</a>
        &nbsp;·&nbsp; ŽďárAI portál
    </div>
</div>
</body>
</html>
