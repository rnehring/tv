<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slides shown in the kiosk slideshow.
 *
 * type:
 *   image       - a static uploaded graphic (image_path)
 *   iframe      - an embedded external page (iframe_url)
 *   birthday    - the auto-generated "this month's birthdays" slide (rendered live)
 *   anniversary - the auto-generated "this month's work anniversaries" slide
 *
 * The birthday/anniversary rows are singletons managed by the app; admins can
 * still toggle them active, set duration, ordering and location targeting, but
 * they are rendered from live data (and auto-hidden when the current month has
 * no matching employees).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('image'); // image | iframe | birthday | anniversary
            $table->text('caption')->nullable();
            $table->string('image_path')->nullable();
            $table->string('iframe_url')->nullable();
            $table->unsignedInteger('duration_ms')->default(8000);
            $table->boolean('is_active')->default(true);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('type');
        });

        // Pivot: which locations a slide is limited to. No rows for a slide
        // means "show everywhere".
        Schema::create('location_slide', function (Blueprint $table) {
            $table->foreignId('slide_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->primary(['slide_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_slide');
        Schema::dropIfExists('slides');
    }
};
