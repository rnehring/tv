<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The background graphic (and a little styling) used behind the auto-generated
 * monthly slides. One row per month per kind (birthday / anniversary).
 * Names are auto-laid-out over this background at display time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_backgrounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('month');        // 1-12
            $table->string('kind');                       // birthday | anniversary
            $table->string('image_path')->nullable();
            $table->string('heading')->nullable();        // optional title override
            $table->string('text_color', 9)->default('#3a2416');   // name colour
            $table->string('accent_color', 9)->default('#c0392b');  // "today" highlight
            $table->string('align', 10)->default('center'); // center | left | right
            $table->timestamps();

            $table->unique(['month', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_backgrounds');
    }
};
