<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Spřátelené akce (pořádají jiné komunity, ne ŽďárAI)
|--------------------------------------------------------------------------
|
| Data pro pozvánky App\Mail\PartnerEventInvite. Zdroj musí být ověřený
| (stránka pořadatele) — nic nedomýšlet. Místo konání uvádějte až po
| zveřejnění pořadatelem.
|
*/

return [

    'ai-monday-zdar-1' => [
        'title' => 'AI Monday Žďár n. S. #1',
        'organizer' => 'AI Monday',
        'organizer_url' => 'https://www.aimonday.cz',
        'url' => 'https://www.aimonday.cz/events/ai-monday-zdar-n-s-1',
        // Odkaz v CTA — se zdrojem, ať pořadatel vidí registrace od nás
        'registration_url' => 'https://www.aimonday.cz/events/ai-monday-zdar-n-s-1?utm_source=zdarai',
        'date' => '2026-10-12',
        'date_label' => 'pondělí 12. října 2026',
        'date_short' => 'po 12. října',
        'time_label' => 'program od 18:00',
        'venue' => 'IT NeŠkola, 1. patro, Dolní 1, 591 01 Žďár nad Sázavou',
        'capacity' => 30,
        'price_label' => 'zdarma',
        'program' => [
            [
                'time' => '18:00',
                'title' => 'Úvod',
            ],
            [
                'time' => '18:10',
                'title' => 'AI tvorba AI chatbota pro Kraj Vysočina',
                'duration' => '30 min',
                'speaker' => 'Martin Hejl',
                'speaker_role' => 'Generalista | AI & digitální tvorba & vibecoding',
                'description' => 'Student vyvíjí chatbota s RAG vyhledáváním pro Kraj Vysočina.',
            ],
            [
                'time' => '18:50',
                'title' => 'AI v každodenní práci: od textů k reálným procesům',
                'duration' => '30 min',
                'speaker' => 'Ondřej Matějka',
                'speaker_role' => 'manažer marketingu, DEL a. s.',
            ],
            [
                'time' => '19:30+',
                'title' => 'Diskuze a networking',
            ],
        ],
    ],

    // Zdroj: https://dny.ai/program/ai-for-all (ověřeno 2. 11. 2026, TF #4151).
    // Jednorázová pozvánka pro segmenty list (CSV účastníků AI Monday Žďár #1),
    // subscribers a registrants — doporučené pořadí rozesílky: list → subscribers → registrants.
    'ai-for-all-jihlava-2026' => [
        'title' => 'AI for All 2026 | Jihlava',
        'organizer' => 'AI Monday Jihlava',
        'organizer_url' => 'https://www.aimonday.cz',
        'url' => 'https://dny.ai/program/ai-for-all',
        'festival' => 'Dny AI',
        'festival_url' => 'https://dny.ai',
        'date' => '2026-11-05',
        'date_label' => 'čtvrtek 5. listopadu 2026',
        'date_short' => 'čt 5. 11.',
        'time_label' => 'AI Monday od 18:00 (do 19:30), festivalový program od 16:30',
        'venue' => 'Historická aula Vysoké školy polytechnické Jihlava, Tolstého 1556',
        'price_label' => 'zdarma, registrace není nutná',
        'guests' => [
            ['name' => 'Milan Lempera', 'role' => 'CTO Commity.cz'],
            ['name' => 'Honza Zbirovský', 'role' => 'spoluzakladatel inqubo.cz', 'link' => ['text' => 'inqubo.cz', 'url' => 'https://inqubo.cz']],
            ['name' => 'Jaroslav Krotký', 'role' => 'IT Kraje Vysočina'],
            ['name' => 'Pavel Vitešník', 'role' => 'všestranný vývojář, grafik a video producent'],
            // Název webu s diakritikou, doména bez (Petr, 2. 11.)
            ['name' => 'Petr Mandík', 'role' => 'spoluzakladatel Uměláinteligence.cz', 'link' => ['text' => 'Uměláinteligence.cz', 'url' => 'https://www.umelainteligence.cz']],
        ],
        // Vlastní šablona a předmět (PartnerEventInvite je bere z configu, jinak výchozí).
        'view' => 'emails.partner-event-ai-for-all-jihlava',
        'subject' => '🎂 AI Monday Jihlava slaví rok — čt 5. 11. na AI for All (Dny AI)',
        // Naše akce zmíněná v boxu na konci (jen pokud je published).
        'promo_event_slug' => 'zdarai-4',
        // Stránka ŽďárAI #4 v programu festivalu (odkaz v boxu „A týden nato ve Žďáru“).
        'promo_event_dnyai_url' => 'https://dny.ai/program/zdarai-4-lokalni-ai-asistent-observabilita-ragu',
        // Zdroj pro dobrovolné přihlášení k newsletteru z mailu pro segment list
        // (NewsletterSubscriber.source; route newsletter.optin).
        'optin_source' => 'ai-monday-1',
    ],

];
