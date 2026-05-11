<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Talk extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'speaker_id',
        'title',
        'description',
        'duration_minutes',
        'sort_order',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TalkTranslation::class);
    }
}
