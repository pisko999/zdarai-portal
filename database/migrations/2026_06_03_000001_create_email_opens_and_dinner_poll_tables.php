<?php

declare(strict_types=1);

use App\Models\Registration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sledování otevření emailu (tracking pixel) — agregovaný řádek na registraci + typ mailu
        Schema::create('email_opens', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Registration::class)->constrained()->cascadeOnDelete();
            $table->string('mailable')->default('first_meetup_info');
            $table->unsignedInteger('open_count')->default(0);
            $table->dateTime('first_opened_at')->nullable();
            $table->dateTime('last_opened_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['registration_id', 'mailable']);
        });

        // Anketa: zájem o večeři/pivo po akci v restauraci Ingot
        Schema::create('dinner_poll_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Registration::class)->constrained()->cascadeOnDelete();
            $table->dateTime('opened_at')->nullable();      // kdy účastník otevřel anketu
            $table->enum('attendance', ['dinner', 'beer', 'none'])->nullable();
            $table->string('food_choice')->nullable();      // vybrané jídlo z lístku
            $table->string('note')->nullable();             // poznámka (alergie apod.)
            $table->dateTime('responded_at')->nullable();   // kdy odeslal odpověď
            $table->timestamps();

            $table->unique('registration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dinner_poll_responses');
        Schema::dropIfExists('email_opens');
    }
};
