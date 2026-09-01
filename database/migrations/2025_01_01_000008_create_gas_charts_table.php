<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Monthly "gift card goal" figures (ported from the legacy gasCharts table).
 * The kiosk renders a goal thermometer from the most recent month that has
 * goals entered.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gas_charts', function (Blueprint $table) {
            $table->id();
            $table->char('year_month', 7)->unique();      // e.g. "2026-08"
            $table->date('updated_on')->nullable();        // "as of" date used for pace
            $table->boolean('finalized')->default(false);
            $table->decimal('goal50', 12, 2)->default(0);
            $table->decimal('goal100', 12, 2)->default(0);
            $table->decimal('goal150', 12, 2)->default(0);
            $table->decimal('goal200', 12, 2)->default(0);
            $table->decimal('achieved', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gas_charts');
    }
};
