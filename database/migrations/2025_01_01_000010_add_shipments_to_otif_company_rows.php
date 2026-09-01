<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the shipment on-time figures that are the real OTIF headline:
 * On-Time % = on-time shipped ÷ total shipped, for the current and previous
 * month. The existing backlog columns stay for the secondary panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otif_company_rows', function (Blueprint $table) {
            $table->unsignedInteger('shipped_total')->default(0)->after('company_name');
            $table->unsignedInteger('shipped_on_time')->default(0)->after('shipped_total');
            $table->unsignedInteger('prev_shipped_total')->default(0)->after('shipped_on_time');
            $table->unsignedInteger('prev_shipped_on_time')->default(0)->after('prev_shipped_total');
        });
    }

    public function down(): void
    {
        Schema::table('otif_company_rows', function (Blueprint $table) {
            $table->dropColumn(['shipped_total', 'shipped_on_time', 'prev_shipped_total', 'prev_shipped_on_time']);
        });
    }
};
