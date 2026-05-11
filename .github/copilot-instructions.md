# ŽďárAI portál — Copilot Instructions

Portál pro AI události pro programátory a technicky zdatnou veřejnost.  
Místo konání: **MtgForFun**, Žďár nad Sázavou.  
Akce každý **první čtvrtek v měsíci**, 2+ přednášky + networking.

## Tech Stack

- **Backend**: Laravel 13, PHP 8.3 (strict types)
- **Frontend**: Livewire 3, Blade, Tailwind CSS v4
- **DB**: MySQL 8.0, Eloquent ORM
- **Auth**: Laravel Breeze (volitelný účet, lze i bez účtu)
- **Queue**: Redis + Laravel Queues (emailové notifikace)
- **Deploy**: VPS, Apache 2.4 + mod_php

## TaskForge Integration

Backlog projektu je spravován v **TaskForge** přes MCP server.  
MCP config: `.vscode/mcp.json` | URL: `https://taskforge.mtgforfun.cz/mcp`

### Agenti
| Jméno | ID | Role | Doména |
|---|---|---|---|
| Copilot CEO | 1 | Orchestrátor | Plánování, koordinace, backlog |
| Copilot Laravel | 68 | Backend/Fullstack | Laravel, Livewire, modely, migrace |
| Copilot DevOps | 69 | DevOps/QA | VPS, Apache 2.4, testy, bezpečnost |

### Povinný task workflow
```
1. list_tasks(status: "open")           → zjisti co dělat
2. transition_task(id, "in_progress")   → označ jako rozpracované
3. ... pracuj na úkolu ...
4. add_comment(id, "Hotovo: ...")       → zapiš co jsi udělal
5. transition_task(id, "in_review")     → předej ke kontrole
```

### Paměťový workflow
```
Na začátku:         semantic_search_memories("relevantní téma")
Během práce:        create_memory(type: "journal", content: "...")
Při použití znalosti: mark_memory_used(memory_id, context: "proč")
```

### Vlny (Sprinty)
| ID | Název | Status |
|---|---|---|
| 58 | Wave 0 — Setup & Infrastructure | active |
| 59 | Wave 1 — Veřejný portál (Core) | planning |
| 60 | Wave 2 — Admin Panel | planning |
| 61 | Wave 3 — Polish & Launch | planning |

## Coding Standards

### PHP / Laravel
- PHP 8.3+, `declare(strict_types=1)` v každém souboru
- Livewire 3 komponenty v `app/Livewire/` (Admin v `app/Livewire/Admin/`)
- Modely v `app/Models/`, používej `$fillable` nebo `$guarded = []`
- Form Requests pro validaci vstupů
- Migrace s rollback (`down()` metoda)
- Factories pro každý model (pro testy a seedery)

### Tailwind CSS
- Mobile-first přístup
- Žádný custom CSS pokud Tailwind postačí
- Dark mode připraven (class-based)

### Bezpečnost (OWASP)
- NIKDY raw SQL — vždy Eloquent / Query Builder s bindings
- Blade `{{ }}` pro výstup (escaped), `{!! !!}` jen kde nutno + sanitize
- Validuj všechny vstupy přes Form Request (nebo `$request->validate()`)
- Rate limiting na registraci a admin login
- File upload: ověř MIME, maximální velikost, uložení mimo `public/`

### Testy
- Framework: **Pest**
- Feature testy v `tests/Feature/`
- Každá nová feature = test
- `php artisan test` musí procházet zeleně

## Databázové schéma (přehled)

```
events          → id, title, slug, description, date, location, capacity, price, status
speakers        → id, name, bio, avatar, github_url, linkedin_url
talks           → id, event_id, speaker_id, title, description, duration_minutes, sort_order
users           → ... (default Breeze) + phone, bio, is_admin
registrations   → id, event_id, user_id (nullable), name, email, token (UUID),
                   payment_status (free/pending/paid/cancelled),
                   reminder_sent_at, email_opt_out, dietary_notes
event_translations  → id, event_id, locale, title, description
talk_translations   → id, talk_id, locale, title, description
```

## Dvojjazyčnost CZ/EN
- Primární jazyk: **CZ**
- Sekundární: **EN**
- Lang soubory: `lang/cs/` a `lang/en/`
- DB překlady: `event_translations`, `talk_translations`
- Fallback: CZ pokud překlad chybí
- SEO: `hreflang` tagy v `<head>`

## Emailové notifikace
- **Potvrzení registrace**: `App\Mail\RegistrationConfirmed` — dispatch přes Queue
- **Připomínka**: 3 dny před akcí, `SendEventReminders` command, daily schedule
- Opt-out odkaz v každém emailu (via registration token)

## Admin Panel
- Route prefix: `/admin`
- Middleware: `IsAdmin` (ověřuje `is_admin = true` na User modelu)
- Rate limit na login: max 5 pokusů / minuta
- Session timeout: 2 hodiny

## Grafická dokumentace postupu

Každý větší krok vývoje = HTML soubor v `documentation/`.  
Cíl: živá vizuální kronika tvorby projektu pro přednášku o TaskForge.

### Pravidla
- **Kdy tvořit**: po každém architektonickém rozhodnutí, dokončené fázi nebo klíčové implementaci
- **Kam ukládat**: `documentation/<NNN>-<slug>.html` (číslováno, aby šlo řadit)
- **Styl**: Dark Tech (gray-950, green-400, JetBrains Mono) — konzistentní s designem portálu
- **Co obsahuje**: popis rozhodnutí/fáze, diagram/vizualizace, timeline krok, odkaz zpět na index
- **Index**: `documentation/index.html` — přehled všech kroků, stav (hotovo/in-progress/plán)
- **TF synchronizace**: komentář k příslušnému tasku + odkaz na soubor

### Stávající dokumentace
| Soubor | Obsah | Stav |
|---|---|---|
| `documentation/001-entity-diagram.html` | ERD diagram — 7 entit, 6 relací | ✅ |

## Důležité poznámky
- Online platební brána **není v scope** (platební status se spravuje ručně)
- První přednáška bude **o TaskForge** — vše se komentuje pro demo při přednášce
- Obsazenost se počítá v reálném čase (DB count, ne cached)
- Race conditions při registraci ošetřit DB transakcí
