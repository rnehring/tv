<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_backgrounds', function (Blueprint $table) {
            // null = auto-fit (default); a value = fixed name font size in px
            $table->unsignedSmallInteger('font_size')->nullable()->after('align');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_backgrounds', function (Blueprint $table) {
            $table->dropColumn('font_size');
        });
    }
};
