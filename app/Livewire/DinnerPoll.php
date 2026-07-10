<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\DinnerPollResponse;
use App\Models\Registration;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class DinnerPoll extends Component
{
    /**
     * Nabídka jídel pro posezení v Restauraci Krystal (ŽďárAI #2).
     * Jediný zdroj pravdy — používá se ve view i v e-mailu s pozvánkou.
     */
    public const FOOD_OPTIONS = [
        '200 g Steak z krkovice, sázené vejce, restovaná slanina, americké brambory — 225 Kč',
        '150 g Svratecký guláš z hovězí svíčkové, 3 ks bramboráčky — 230 Kč',
        '150 g Kuřecí steak na roštu podávaný krájený na restované rýži se zeleninou, sypaný sýrem Grand Moravia — 215 Kč',
        '150 g Vepřová játra na roštu, farmářské hranolky, tatarská omáčka — 185 Kč',
        '300 g Caesar salát s kuřecím masem, krutony a sýrem Grand Moravia — 175 Kč',
    ];

    /** Hodnota ukládaná do DB při volbě „jiné jídlo" (upřesnění v poznámce). */
    public const OTHER_CHOICE = 'Jiné (viz poznámka)';

    /** Text zobrazený u volby „jiné" v anketě. */
    public const OTHER_LABEL = '🍴 Jiné / klasika (řízek, smažák…) — upřesni v poznámce';

    public Registration $registration;

    public string $attendance = '';

    public string $foodChoice = '';

    public string $note = '';

    public bool $submitted = false;

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'attendance' => ['required', 'in:dinner,beer,none'],
            'foodChoice' => [
                'nullable',
                'required_if:attendance,dinner',
                Rule::in([...self::FOOD_OPTIONS, self::OTHER_CHOICE]),
            ],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'foodChoice.required_if' => 'Vyber prosím jídlo (nebo volbu „Jiné").',
            'foodChoice.in' => 'Vyber prosím jídlo z aktuální nabídky.',
        ];
    }

    public function mount(Registration $registration): void
    {
        $this->registration = $registration;

        $poll = $this->registration->dinnerPoll;
        if ($poll && $poll->responded_at) {
            $this->attendance = $poll->attendance ?? '';
            // Zpětná kompatibilita: starší odpovědi (akce #1) obsahují volný text —
            // property se naplní beze změny, jen nebude předvybraná žádná radio volba.
            $this->foodChoice = $poll->food_choice ?? '';
            $this->note = $poll->note ?? '';
            $this->submitted = true;
        }
    }

    public function submit(): void
    {
        $this->validate();

        DinnerPollResponse::updateOrCreate(
            ['registration_id' => $this->registration->id],
            [
                'attendance' => $this->attendance,
                'food_choice' => $this->attendance === 'dinner' ? $this->foodChoice : null,
                'note' => $this->note,
                'responded_at' => now(),
            ]
        );

        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.dinner-poll', [
            'foodOptions' => self::FOOD_OPTIONS,
            'otherChoice' => self::OTHER_CHOICE,
            'otherLabel' => self::OTHER_LABEL,
        ]);
    }
}
