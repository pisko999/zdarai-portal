<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('token')->unique();
            $table->string('name');
            $table->string('email');
            $table->enum('payment_status', ['free', 'pending', 'paid', 'cancelled'])->default('free');
            $table->dateTime('reminder_sent_at')->nullable();
            $table->boolean('email_opt_out')->default(false);
            $table->string('dietary_notes')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
