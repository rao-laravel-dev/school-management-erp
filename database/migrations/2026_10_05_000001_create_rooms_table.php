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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_no')->unique();
            $table->string('name')->nullable();
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('type')->nullable(); // classroom, lab, hall, library, other
            $table->foreignId('school_class_id')->nullable()->constrained('school_class')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->boolean('status')->default(1);
            $table->timestamps();

            // Ek class+section ka sirf ek room (NULL wali rows, jaise Lab/Hall, duplicate nahi maani jatin)
            $table->unique(['school_class_id', 'section_id'], 'rooms_class_section_unique');
        });

        // class_timetables.room_id FK yahan hai: class_timetables (2026_08_02) is table se pehle banta hai
        Schema::table('class_timetables', function (Blueprint $table) {
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_timetables', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
        });

        Schema::dropIfExists('rooms');
    }
};
