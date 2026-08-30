<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_timings', function (Blueprint $table) {
            $table->id();
            $table->string('season_name');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('late_grace_minutes')->default(0);
            $table->boolean('is_active')->default(false);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_timings');
    }
};
