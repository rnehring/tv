<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cached OTIF / order-backlog snapshots. A scheduled job pulls the backlog from
 * Epicor and writes one snapshot (with a row per business unit); the kiosk slide
 * renders from the most recent snapshot so it never waits on the ERP.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otif_snapshots', function (Blueprint $table) {
            $table->id();
            $table->timestamp('captured_at');
            $table->string('source')->default('epicor'); // epicor | sample
            $table->string('error')->nullable();          // last fetch error, if any
            $table->timestamps();

            $table->index('captured_at');
        });

        Schema::create('otif_company_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('otif_snapshot_id')->constrained()->cascadeOnDelete();
            $table->string('company_code');
            $table->string('company_name');
            $table->unsignedInteger('open_orders')->default(0);
            $table->unsignedInteger('past_due')->default(0);
            $table->unsignedInteger('due_today')->default(0);
            $table->unsignedInteger('d1_7')->default(0);
            $table->unsignedInteger('d8_14')->default(0);
            $table->unsignedInteger('d15_21')->default(0);
            $table->unsignedInteger('d22_28')->default(0);
            $table->unsignedInteger('d29_plus')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otif_company_rows');
        Schema::dropIfExists('otif_snapshots');
    }
};
