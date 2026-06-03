<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vítej v týmu ŽďárAI</title>
    <style>
        body { font-family: monospace; background: #f9fafb; color: #111827; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; }
        .logo { color: #15803d; font-size: 22px; font-weight: bold; margin: 0 0 2px; }
        .subtitle { color: #6b7280; font-size: 13px; margin: 0 0 32px; }
        p { color: #374151; line-height: 1.7; margin: 0 0 16px; }
        .card { border: 1px solid #bbf7d0; border-radius: 8px; padding: 24px; margin: 24px 0; background: #f0fdf4; }
        .card-row { margin-bottom: 12px; font-size: 14px; }
        .card-label { color: #6b7280; display: block; margin-bottom: 2px; }
        .card-value { color: #111827; font-weight: bold; font-size: 15px; word-break: break-all; }
        .cta { display: inline-block; background: #16a34a; color: #ffffff !important; font-weight: bold; text-decoration: none; padding: 14px 28px; border-radius: 6px; margin: 20px 0; font-size: 14px; }
        a { color: #16a34a; }
        .footer { margin-top: 48px; padding-top: 24px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #9ca3af; line-height: 1.8; }
        .warn { border-left: 3px solid #d97706; background: #fffbeb; padding: 12px 16px; margin: 16px 0; border-radius: 0 6px 6px 0; font-size: 13px; color: #92400e; }
    </style>
</head>
<body bgcolor="#f9fafb" style="font-family: monospace; background: #f9fafb; margin: 0; padding: 0;">
<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f9fafb" style="background:#f9fafb;">
<tr><td style="padding: 40px 20px;">
<div class="container">
    <p class="logo">&gt;_ ŽďárAI</p>
    <p class="subtitle">AI události pro vývojáře &middot; Hernice, Žďár nad Sázavou</p>

    <p>Ahoj <strong>{{ $name }}</strong>,</p>
    <p>vítej v týmu ŽďárAI! Jako <strong>přednášející</strong> máš nově vlastní účet do portálu. Tady jsou tvoje přihlašovací údaje:</p>

    <div class="card">
        <div class="card-row">
            <span class="card-label">📧 Přihlašovací e-mail</span>
            <span class="card-value">{{ $email }}</span>
        </div>
        <div class="card-row">
            <span class="card-label">🔑 Heslo</span>
            <span class="card-value">{{ $password }}</span>
        </div>
    </div>

    <a href="{{ $loginUrl }}" class="cta">Přihlásit se do portálu &rarr;</a>

    <div class="warn">
        🔐 Z bezpečnostních důvodů si po prvním přihlášení heslo prosím změň. Tento e-mail s heslem si po přihlášení smaž.
    </div>

    <p>Pokud budeš cokoli potřebovat — podklady k přednášce, úpravu profilu nebo cokoli kolem akce — stačí odpovědět na tento e-mail.</p>

    <p style="margin-top: 32px;">Těším se na spolupráci!<br>
    <strong>Klára</strong> &middot; AI agent ŽďárAI 🤖</p>

    <div class="footer">
        Reply-to: klara@zdarai.cz &middot; Organizační tým ŽďárAI &middot; Hernice, Žďár nad Sázavou<br>
        Tento e-mail obsahuje přihlašovací údaje — nakládej s ním prosím opatrně.
    </div>
</div>
</td></tr>
</table>
</body>
</html>
