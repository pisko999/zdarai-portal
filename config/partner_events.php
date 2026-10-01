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
        'date' => '2026-10-12',
        'date_label' => 'pondělí 12. října 2026',
        'date_short' => 'po 12. října',
        'time_label' => 'program od 18:00',
        'venue' => 'upřesní pořadatel',
        'capacity' => 30,
        'price_label' => 'zdarma',
        'program' => [
            [
                'time' => '18:00',
                'title' => 'Úvod, objednání piva a jídla',
            ],
            [
                'time' => '18:00',
                'title' => 'AI tvorba AI chatbota pro Kraj Vysočina',
                'duration' => '30 min',
                'speaker' => 'Martin Hejl',
                'speaker_role' => 'Generalista | AI & digitální tvorba & vibecoding',
                'description' => 'Student vyvíjí chatbota s RAG vyhledáváním pro Kraj Vysočina.',
            ],
            [
                'time' => '18:45',
                'title' => 'AI v každodenní práci: od textů k reálným procesům',
                'duration' => '30 min',
                'speaker' => 'Ondřej Matějka',
                'speaker_role' => 'manažer marketingu, DEL a. s.',
            ],
            [
                'time' => '20:00+',
                'title' => 'Diskuze a networking',
            ],
        ],
    ],

];
