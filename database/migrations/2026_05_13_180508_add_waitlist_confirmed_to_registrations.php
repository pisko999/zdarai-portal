<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL: rozšíření ENUM o nové hodnoty
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registrations MODIFY COLUMN payment_status ENUM('waitlist','free','pending','paid','confirmed','cancelled') DEFAULT 'free'");
        }
        // SQLite (testy): ENUM je uložen jako string, žádná změna není nutná
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registrations MODIFY COLUMN payment_status ENUM('free','pending','paid','cancelled') DEFAULT 'free'");
        }
    }
};
