<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'date',
        'location',
        'capacity',
        'price',
        'status',
    ];

    protected $casts = [
        'date' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function talks(): HasMany
    {
        return $this->hasMany(Talk::class)->orderBy('sort_order');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(EventTranslation::class);
    }

    public function registrationCount(): int
    {
        return $this->registrations()->whereNotIn('payment_status', ['cancelled', 'waitlist'])->count();
    }

    public function waitlistCount(): int
    {
        return $this->registrations()->where('payment_status', 'waitlist')->count();
    }

    public function isFull(): bool
    {
        return $this->registrationCount() >= $this->capacity;
    }

    public function getTranslation(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $translation = $this->translations->firstWhere('locale', $locale);
        if ($translation && !empty($translation->$field)) {
            return $translation->$field;
        }
        // fallback to CS (primary)
        if ($locale !== 'cs') {
            $translation = $this->translations->firstWhere('locale', 'cs');
            if ($translation && !empty($translation->$field)) {
                return $translation->$field;
            }
        }
        // ultimate fallback to main column
        return $this->attributes[$field] ?? null;
    }

    public function translationForLocale(string $locale = 'cs'): ?EventTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }
}
