<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Speaker;
use App\Models\Talk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@zdarai.cz',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // 2 speakers
        $speaker1 = Speaker::factory()->create([
            'name' => 'Jan Novák',
            'bio' => 'Vývojář a AI nadšenec. Pracuje s LLM modely v praxi.',
            'github_url' => 'https://github.com/jannovak',
        ]);

        $speaker2 = Speaker::factory()->create([
            'name' => 'Eva Procházková',
            'bio' => 'Data scientist se zaměřením na NLP a strojové učení.',
            'github_url' => 'https://github.com/evaprochazkova',
        ]);

        // 3 events
        $publishedEvent = Event::factory()->published()->create([
            'title' => 'ŽďárAI #1 — Úvod do AI agentů',
            'slug' => 'zdarai-1-uvod-do-ai-agentu',
            'description' => 'První setkání ŽďárAI komunity. Povídáme si o AI agentech a jejich praktickém využití.',
            'date' => now()->addDays(14),
            'capacity' => 25,
        ]);

        $draftEvent = Event::factory()->draft()->create([
            'title' => 'ŽďárAI #2 — LLM v praxi',
            'slug' => 'zdarai-2-llm-v-praxi',
            'description' => 'Druhé setkání zaměřené na praktické využití velkých jazykových modelů.',
            'date' => now()->addDays(45),
            'capacity' => 30,
        ]);

        $pastEvent = Event::factory()->past()->create([
            'title' => 'ŽďárAI #0 — Kick-off',
            'slug' => 'zdarai-0-kick-off',
            'description' => 'Úvodní setkání komunity, kde jsme si řekli, o čem ŽďárAI bude.',
            'date' => now()->subDays(30),
            'capacity' => 20,
        ]);

        // Talks for published event
        Talk::factory()->create([
            'event_id' => $publishedEvent->id,
            'speaker_id' => $speaker1->id,
            'title' => 'TaskForge: Správa projektů s AI agenty',
            'description' => 'Jak jsme postavili tento portál pomocí AI agentů a TaskForge systému.',
            'duration_minutes' => 45,
            'sort_order' => 1,
        ]);

        Talk::factory()->create([
            'event_id' => $publishedEvent->id,
            'speaker_id' => $speaker2->id,
            'title' => 'LLM embeddings pro každého',
            'description' => 'Praktická ukázka sémantického vyhledávání pomocí embeddingů.',
            'duration_minutes' => 30,
            'sort_order' => 2,
        ]);

        // Talks for draft event
        Talk::factory()->create([
            'event_id' => $draftEvent->id,
            'speaker_id' => $speaker1->id,
            'title' => 'Jak psát prompty pro GPT-4',
            'duration_minutes' => 45,
            'sort_order' => 1,
        ]);

        Talk::factory()->create([
            'event_id' => $draftEvent->id,
            'speaker_id' => $speaker2->id,
            'title' => 'Fine-tuning vs. RAG: Kdy co použít',
            'duration_minutes' => 30,
            'sort_order' => 2,
        ]);

        // Talks for past event
        Talk::factory()->create([
            'event_id' => $pastEvent->id,
            'speaker_id' => $speaker1->id,
            'title' => 'Co je ŽďárAI a proč vzniklo',
            'duration_minutes' => 20,
            'sort_order' => 1,
        ]);

        Talk::factory()->create([
            'event_id' => $pastEvent->id,
            'speaker_id' => $speaker2->id,
            'title' => 'Stav AI v roce 2026',
            'duration_minutes' => 45,
            'sort_order' => 2,
        ]);

        // 5 registrations on published event
        $registrationData = [
            ['name' => 'Petr Svoboda', 'email' => 'petr.svoboda@example.cz'],
            ['name' => 'Lucie Marková', 'email' => 'lucie.markova@example.cz'],
            ['name' => 'Tomáš Dvořák', 'email' => 'tomas.dvorak@example.cz'],
            ['name' => 'Monika Horáková', 'email' => 'monika.horakova@example.cz'],
            ['name' => 'Martin Blažek', 'email' => 'martin.blazek@example.cz'],
        ];

        foreach ($registrationData as $data) {
            Registration::factory()->create([
                'event_id' => $publishedEvent->id,
                'user_id' => null,
                'name' => $data['name'],
                'email' => $data['email'],
                'payment_status' => 'free',
                'token' => (string) Str::uuid(),
            ]);
        }
    }
}
