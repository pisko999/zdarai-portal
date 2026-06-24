<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nový odkaz na anketu</title>
    <style>
        body { font-family: monospace; background: #f9fafb; color: #111827; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; }
        .logo { color: #15803d; font-size: 22px; font-weight: bold; margin: 0 0 2px; }
        .subtitle { color: #6b7280; font-size: 13px; margin: 0 0 32px; }
        p { color: #374151; line-height: 1.7; margin: 0 0 16px; }
        .cta { display: inline-block; background: #16a34a; color: #ffffff !important; font-weight: bold; text-decoration: none; padding: 14px 28px; border-radius: 6px; margin: 20px 0; font-size: 14px; }
        a { color: #16a34a; }
        .footer { margin-top: 48px; padding-top: 24px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #9ca3af; line-height: 1.8; }
    </style>
</head>
<body bgcolor="#f9fafb" style="font-family: monospace; background: #f9fafb; margin: 0; padding: 0;">
<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f9fafb" style="background:#f9fafb;">
<tr><td style="padding: 40px 20px;">
<div class="container">
    <p class="logo">&gt;_ ŽďárAI</p>
    <p class="subtitle">AI události pro programátory a AI nadšence &middot; IT NeŠkola, Žďár nad Sázavou</p>

    <p>Ahoj <strong>{{ $name }}</strong>,</p>
    <p>omlouvám se 🙏 — při úpravě registrací přednášejících se ti změnil odkaz na anketu, takže ten původní už nefunguje. Tady je <strong>nový, funkční odkaz</strong>:</p>

    <a href="{{ $pollUrl }}" class="cta">Hlasovat v anketě (večeře / pivo) &rarr;</a>

    <p style="font-size: 13px; color: #6b7280;">Pokud jsi v anketě už hlasoval/a před touto opravou, dej prosím hlas znovu přes nový odkaz — staré odpovědi se k nové registraci nepřenesly.</p>

    <p style="margin-top: 32px;">Díky za pochopení a těším se na čtvrtek!<br>
    <strong>Klára</strong> &middot; AI agent ŽďárAI 🤖</p>

    <div class="footer">
        Reply-to: klara@zdarai.cz &middot; Organizační tým ŽďárAI &middot; IT NeŠkola, Žďár nad Sázavou
    </div>
</div>
</td></tr>
</table>
</body>
</html>
