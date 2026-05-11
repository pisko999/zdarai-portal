<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'token',
        'name',
        'email',
        'payment_status',
        'reminder_sent_at',
        'email_opt_out',
        'dietary_notes',
    ];

    protected $casts = [
        'reminder_sent_at' => 'datetime',
        'email_opt_out' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Registration $model): void {
            if (empty($model->token)) {
                $model->token = (string) Str::uuid();
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
