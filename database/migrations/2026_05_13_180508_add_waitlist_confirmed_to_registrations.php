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
        // MySQL: rozšíření nativního ENUM o nové hodnoty.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registrations MODIFY COLUMN payment_status ENUM('waitlist','free','pending','paid','confirmed','cancelled') DEFAULT 'free'");

            return;
        }

        // Ostatní drivery (SQLite v testech): Laravel mapuje enum() na CHECK
        // constraint, takže ho musíme přegenerovat se širší sadou hodnot.
        Schema::table('registrations', function (Blueprint $table) {
            $table->enum('payment_status', [
                'waitlist', 'free', 'pending', 'paid', 'confirmed', 'cancelled',
            ])->default('free')->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registrations MODIFY COLUMN payment_status ENUM('free','pending','paid','cancelled') DEFAULT 'free'");

            return;
        }

        Schema::table('registrations', function (Blueprint $table) {
            $table->enum('payment_status', [
                'free', 'pending', 'paid', 'cancelled',
            ])->default('free')->change();
        });
    }
};
