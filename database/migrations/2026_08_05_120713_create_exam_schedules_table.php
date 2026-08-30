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
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_class')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->date('date');
            $table->time('time_from');
            $table->time('time_to');
 
            // default+override: auto-filled from class_subject.full_marks/pass_marks,
            // but stored here per-exam so it can be overridden
            $table->integer('max_marks');
            $table->integer('passing_marks');
            $table->timestamps();
 
            // most common lookup: "give me this exam's full schedule for a class"
            $table->index(['exam_id', 'class_id']);
 
            // prevent duplicate subject entries for the same exam+class
            $table->unique(['exam_id', 'class_id', 'subject_id'], 'exam_class_subject_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
