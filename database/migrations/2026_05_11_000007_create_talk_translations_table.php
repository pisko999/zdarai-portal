<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talk_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talk_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['talk_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talk_translations');
    }
};
