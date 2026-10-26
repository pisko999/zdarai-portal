<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Evidence rozeslaných newsletterů k akci (TF #3933).
 * unique(event_id, newsletter_subscriber_id) = každý odběratel max. jednou na akci,
 * i při opakovaném nebo souběžném běhu `newsletter:send`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_event_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_subscriber_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['event_id', 'newsletter_subscriber_id'], 'newsletter_event_sends_event_subscriber_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_event_sends');
    }
};
