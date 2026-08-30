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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            // 🔗 Relational Foreign Keys (Exact matching your actual tables)
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('school_class')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null');

            // 📝 Dynamic Academic Info
            $table->string('roll_no'); // Roll number har saal badal sakta hai

            // ⚙️ Enrollment Status (0 = Inactive, 1 = Active, 2 = Promoted, 3 = Detained, 4 = Pending Approval)
            // Default 4 hai (Pending Approval) jab tak admin. accept na kare
            $table->tinyInteger('enroll_status')->default(4);

            $table->timestamps();

            // 🗑️ Soft Delete Support
            $table->softDeletes();

            // Rule 1: Ek student pure saal mein sirf ek hi class/enrollment rakh sakta hai
            $table->unique(['student_id', 'academic_year_id'], 'student_yearly_unique');

            // Rule 2: Ek hi class, section aur saal mein roll number unique hona chahiye (Aap wala rule)
            $table->unique(['academic_year_id', 'class_id', 'section_id', 'roll_no'], 'academic_roll_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
