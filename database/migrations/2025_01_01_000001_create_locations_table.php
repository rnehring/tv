<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Locations (display zones). Replaces the legacy IP-octet targeting
 * (Kentwood = 10.x, Houston = 192.x, factory floor, etc.). A kiosk requests
 * the slideshow for a zone via ?location=slug, or it is auto-detected from the
 * client IP using `ip_pattern`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            // Client IP prefix used to auto-detect this zone (e.g. "10." or "192.168.").
            $table->string('ip_pattern')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
