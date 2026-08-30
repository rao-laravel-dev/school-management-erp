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
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_class')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_timetable_id')->nullable()->constrained('class_timetables')->nullOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->string('sub_topic')->nullable();
            $table->date('date');
            $table->time('time_from')->nullable();
            $table->time('time_to')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('lecture_video')->nullable();
            $table->string('attachment')->nullable();
            $table->string('teaching_method')->nullable();
            $table->text('general_objectives')->nullable();
            $table->text('previous_knowledge')->nullable();
            $table->text('comprehensive_questions')->nullable();
            $table->longText('presentation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_plans');
    }
};
