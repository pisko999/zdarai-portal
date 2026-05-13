<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('dietary_notes');
            $table->enum('ai_level', ['beginner', 'intermediate', 'advanced', 'expert'])->nullable()->after('email_opt_out');
            $table->string('organization', 255)->nullable()->after('ai_level');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['ai_level', 'organization']);
            $table->string('dietary_notes')->nullable();
        });
    }
};
