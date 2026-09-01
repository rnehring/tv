<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_birthdays', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->unsignedTinyInteger('month');   // 1-12
            $table->unsignedTinyInteger('day');     // 1-31
            $table->string('department')->nullable();
            // Stable key used to de-duplicate rows across repeated CSV imports.
            $table->string('source_key')->nullable()->unique();
            $table->date('imported_on')->nullable();
            $table->timestamps();

            $table->index(['month', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_birthdays');
    }
};
