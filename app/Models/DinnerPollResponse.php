<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DinnerPollResponse extends Model
{
    protected $fillable = [
        'registration_id',
        'opened_at',
        'attendance',
        'food_choice',
        'note',
        'responded_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function attendanceLabel(): string
    {
        return match ($this->attendance) {
            'dinner' => 'Přijdu na večeři',
            'beer' => 'Přijdu jen na pivo',
            'none' => 'Nemám zájem',
            default => '—',
        };
    }
}
