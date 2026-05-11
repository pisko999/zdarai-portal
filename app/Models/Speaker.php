<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Speaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bio',
        'avatar',
        'github_url',
        'linkedin_url',
        'twitter_url',
    ];

    public function talks(): HasMany
    {
        return $this->hasMany(Talk::class);
    }
}
