<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalkTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'talk_id',
        'locale',
        'title',
        'description',
    ];

    public function talk(): BelongsTo
    {
        return $this->belongsTo(Talk::class);
    }
}
