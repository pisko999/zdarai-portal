# ŽďárAI portál

Portál pro AI události pro programátory a technicky zdatnou veřejnost.  
Místo: **MtgForFun**, Žďár nad Sázavou · Akce každý **první čtvrtek v měsíci**.

## Tech Stack

- **Backend**: Laravel 13, PHP 8.3
- **Frontend**: Livewire 3, Blade, Tailwind CSS v4
- **DB**: MySQL 8.0
- **Auth**: Laravel Breeze
- **Queue**: Redis + Laravel Queues
- **Deploy**: VPS, Apache 2.4 + mod_php

## Vývoj projektu

Projekt je řízen přes **TaskForge** s GitHub Copilot Agents (CEO, Laravel, DevOps).  
Postup vývoje je dokumentován v [`documentation/`](documentation/) — otevři `documentation/index.html`.

## Šablony designu

Vizuální návrhy portálu jsou v [`sablony/`](sablony/) — otevři `sablony/index.html`.  
Zvolený design: **Dark Tech** (01).

## Instalace (po nastavení prostředí)

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```
