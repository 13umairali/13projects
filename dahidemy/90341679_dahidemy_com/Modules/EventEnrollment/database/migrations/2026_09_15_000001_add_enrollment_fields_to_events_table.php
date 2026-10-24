<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive columns on the existing `events` table (Modules\Academic\Models\Event).
 * The Event model itself is untouched — Eloquent picks these up automatically
 * since it has no explicit $fillable/$guarded restricting them (it uses
 * $guarded = ['id'], so every other column, including these, is mass-assignable).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('location');
            $table->decimal('price', 10, 2)->nullable()->after('is_paid');
            $table->string('currency_code', 10)->nullable()->after('price');
            $table->unsignedInteger('max_seats')->nullable()->after('currency_code');
            $table->boolean('enrollment_enabled')->default(true)->after('max_seats');
            $table->dateTime('registration_deadline')->nullable()->after('enrollment_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'price', 'currency_code', 'max_seats', 'enrollment_enabled', 'registration_deadline']);
        });
    }
};
