<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentLog extends Model
{
    use HasFactory;

    /**
     * Pouze created_at je relevantní — souhlas je nemněnný audit záznam.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'ip_hash',
        'categories',
        'consent_version',
        'user_agent',
    ];

    protected $casts = [
        'categories' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
