<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Audit log souhlasů s cookies (GDPR / ePrivacy). NEukládáme raw IP — jen SHA-256 hash.
        Schema::create('consent_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->string('ip_hash', 64)->nullable();      // SHA-256(IP + app key) — GDPR minimalizace
            $table->json('categories');                     // {"necessary":true,"analytics":false}
            $table->string('consent_version');
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
    }
};
