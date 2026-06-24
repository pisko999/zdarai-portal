<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $isToday ? 'Dnes večer' : 'Už zítra' }} — {{ $registration->event->title }}</title>
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
        .cta { display: inline-block; background: #16a34a; color: #ffffff !important; font-weight: bold; text-decoration: none; padding: 14px 28px; border-radius: 6px; margin: 8px 0; font-size: 14px; }
        a { color: #16a34a; }
        .footer { margin-top: 48px; padding-top: 24px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #9ca3af; line-height: 1.8; }
        .badge { display: inline-block; background: #16a34a; color: #ffffff; font-size: 11px; font-weight: bold; padding: 2px 8px; border-radius: 4px; }
        .info-box { border-left: 3px solid #16a34a; background: #f0fdf4; padding: 12px 16px; margin: 16px 0; border-radius: 0 6px 6px 0; font-size: 14px; }
        .warn-box { border-left: 3px solid #d97706; background: #fffbeb; padding: 12px 16px; margin: 16px 0; border-radius: 0 6px 6px 0; font-size: 14px; color: #92400e; }
        .map { width: 100%; max-width: 600px; border: 1px solid #e5e7eb; border-radius: 8px; display: block; margin: 8px 0; }
        h2 { color: #15803d; font-size: 16px; margin: 32px 0 8px; }
    </style>
</head>
<body bgcolor="#f9fafb" style="font-family: monospace; background: #f9fafb; margin: 0; padding: 0;">
<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f9fafb" style="background:#f9fafb;">
<tr><td style="padding: 40px 20px;">
<div class="container">
    <p class="logo">&gt;_ ŽďárAI</p>
    <p class="subtitle">AI události pro programátory a AI nadšence &middot; ŽďárAI</p>

    <p>Dobrý den, <strong>{{ $registration->name }}</strong>,</p>
    <p>{{ $isToday ? 'právě <strong>dnes večer</strong>' : 'už <strong>zítra</strong>' }} se vidíme na akci <strong>{{ $registration->event->title }}</strong>! 🎉
       Posíláme pár praktických informací, ať vše klapne.</p>

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
            <span class="card-value">{{ $registration->event->location ?? 'IT NeŠkola, Dolní 1, Žďár nad Sázavou' }}</span>
        </div>
    </div>

    <h2>�️ Půjdeme po akci na večeři / pivo?</h2>
    <p>Po programu plánujeme společný posezení v <strong>Restauraci Ingot</strong>
       (<a href="https://ingot.ala-gastro.cz">ingot.ala-gastro.cz</a>) — kousek od místa konání.
       Ať víme, pro kolik lidí zarezervovat místa, dej nám prosím vědět v krátké anketě:</p>

    <div class="warn-box">
        ⏰ <strong>Kuchyně vaří jen do 19:45</strong>, takže pokud chceš večeřet, je potřeba si jídlo
        <strong>objednat dopředu</strong>. V anketě proto rovnou vyber jídlo z
        <a href="https://cdn.website.dish.co/media/c2/36/9986858/JIDELNI-LISTEK.pdf">jídelního lístku</a>.
    </div>

    <a href="{{ $pollUrl }}" class="cta">Vyplnit anketu (večeře / pivo) &rarr;</a>

    <p style="font-size: 13px; color: #6b7280;">Anketa zabere 30 vteřin a hodně nám pomůže s rezervací.</p>

    <h2>🅿️ Jak se k nám dostanete</h2>
    <p>Zaparkujte na parkovišti <strong>před obchodem Maxi Hit</strong> (modrý bod <strong>P</strong> na mapě).
       Odtud to vezměte pěšky okolo budovy až ke <strong>vchodu do Hernice</strong> (červený bod <strong>C</strong>).
       Cesta je vyznačená zeleně.</p>

    <img src="{{ $message->embed(public_path('images/email/mapa-hernice.png')) }}" alt="Mapa — parkoviště u Maxi Hit (P) a vchod do Hernice (C)" class="map">

    <a href="{{ $eventUrl }}" style="color:#16a34a; font-size: 13px;">Zobrazit detail akce &rarr;</a>

    <p style="margin-top: 32px;">Těšíme se na vás!</p>
    <p style="margin: 0 0 4px;"><strong>Klára</strong> &middot; AI agent ŽďárAI 🤖</p>
    <p style="margin: 0; font-size: 12px; color: #6b7280;">Reply-to: klara@zdarai.cz &middot; Organizační tým ŽďárAI</p>

    <div class="footer">
        Tuto zprávu jste obdrželi, protože jste se zaregistrovali na akci ŽďárAI.<br>
        Pro zlepšení organizace sledujeme otevření tohoto e-mailu a ankety.
        Nechcete dostávat další zprávy?
        <a href="{{ $optOutUrl }}">Odhlásit se</a>
        &nbsp;&middot;&nbsp; ŽďárAI portál
    </div>
</div>
{{-- Tracking pixel (otevření e-mailu) --}}
<img src="{{ $pixelUrl }}" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0;">
</td></tr>
</table>
</body>
</html>
