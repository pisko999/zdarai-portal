<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Připomínka akce</title>
    <style>
        body { font-family: monospace; background: #0a0a0f; color: #86efac; margin: 0; padding: 40px 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        h1 { color: #4ade80; }
        .card { border: 1px solid #14532d; border-radius: 8px; padding: 24px; margin: 20px 0; background: #0f1a0f; }
        a { color: #4ade80; }
        .cta { display: inline-block; background: #16a34a; color: #0a0a0f; font-weight: bold; text-decoration: none; padding: 12px 24px; border-radius: 6px; margin: 16px 0; }
        .footer { margin-top: 40px; font-size: 11px; color: #166534; }
    </style>
</head>
<body>
<div class="container">
    <h1>&gt;_ ŽďárAI</h1>
    <p>Dobrý den, <strong>{{ $registration->name }}</strong>,</p>
    <p>Připomínáme vám, že akce <strong>{{ $registration->event->title }}</strong> se koná <strong>za 3 dny</strong>.</p>

    <div class="card">
        <strong>📅 Datum:</strong> {{ $registration->event->date->format('j. n. Y H:i') }}<br>
        <strong>📍 Místo:</strong> {{ $registration->event->location ?? 'MtgForFun, Žďár nad Sázavou' }}<br>
        @if($registration->event->price)
        <strong>💰 Vstupné:</strong> {{ number_format($registration->event->price, 0) }} Kč<br>
        @else
        <strong>💰 Vstupné:</strong> Zdarma<br>
        @endif
    </div>

    <a href="{{ url('/udalosti/' . $registration->event->slug) }}" class="cta">Zobrazit detail akce →</a>

    <p>Těšíme se na vás!</p>

    <div class="footer">
        Chcete odhlásit odběr připomínek?
        <a href="{{ url('/opt-out/' . $registration->token) }}">Odhlásit se</a>
        &nbsp;·&nbsp; ŽďárAI portál
    </div>
</div>
</body>
</html>
