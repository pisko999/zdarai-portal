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

];
