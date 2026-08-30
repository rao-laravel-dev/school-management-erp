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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_class')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->decimal('total_marks', 8, 2);
            $table->decimal('obtained_marks', 8, 2);
            $table->decimal('percentage', 5, 2);
            $table->foreignId('grade_id')->nullable()->constrained('marks_grades')->nullOnDelete();
            $table->unsignedInteger('position')->nullable();
            $table->enum('status', ['Pass', 'Fail'])->default('Fail');
            $table->timestamps();

            // hot query path: filter page load (Exam+Class+Section combo)
            $table->index(['exam_id', 'class_id', 'section_id']);

            // individual FK indexes — needed for joins/lookups that don't use the composite above
            $table->index('enrollment_id');
            $table->index('grade_id');

            // searchable/filterable column — used for Pass/Fail count widgets, filtering
            $table->index('status');

            // one result per student per exam
            $table->unique(['exam_id', 'enrollment_id'], 'exam_enrollment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
