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
        Schema::create('class_timetables', function (Blueprint $table) {
            $table->id();
            // History hai (lesson plans link): saal delete hone par timetable na mite
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('school_class_id')->constrained('school_class')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            /* old: $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete(); */
            // value teachers.id hai (users nahi)
            $table->foreignId('teacher_id')->constrained('teachers')->restrictOnDelete();
            // FK create_rooms mein hai, kyun ke rooms table is se baad mein banta hai (same as fee_assignment_id)
            $table->unsignedBigInteger('room_id')->nullable();
            $table->enum('day', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->time('time_from');
            $table->time('time_to');
            $table->timestamps();

            // getData / save queries: year + class + section + day
            $table->index(['academic_year_id', 'school_class_id', 'section_id', 'day'], 'class_timetables_year_class_section_day_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_timetables');
    }
};
