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
        Schema::create('marksheet_templates', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('template_name')->index();   // 👈 searchable/listed in DataTable
            $table->string('exam_name')->nullable();
            $table->string('school_name')->nullable();
            $table->string('exam_center')->nullable();
            $table->text('body_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->date('printing_date')->nullable();

            // Uploaded images (filename only, via ImageService)
            $table->string('header_image')->nullable();
            $table->string('left_logo')->nullable();
            $table->string('right_logo')->nullable();
            $table->string('left_sign')->nullable();
            $table->string('middle_sign')->nullable();
            $table->string('right_sign')->nullable();
            $table->string('background_image')->nullable();

            // Field toggles (ON/OFF switches)
            $table->boolean('show_name')->default(true);
            $table->boolean('show_father_name')->default(true);
            $table->boolean('show_mother_name')->default(false);
            $table->boolean('show_exam_session')->default(true);
            $table->boolean('show_admission_no')->default(true);
            $table->boolean('show_division')->default(false);
            $table->boolean('show_rank')->default(false);
            $table->boolean('show_roll_number')->default(true);
            $table->boolean('show_photo')->default(true);
            $table->boolean('show_class')->default(true);
            $table->boolean('show_section')->default(true);
            $table->boolean('show_dob')->default(false);
            $table->boolean('show_remark')->default(true);

            // Print design: design + orientation, own colours (null = school theme colour), school branding from Site Settings (off for old rows)
            $table->string('design', 20)->default('classic');
            $table->string('orientation', 10)->default('portrait');
            $table->string('primary_color', 7)->nullable();
            $table->string('accent_color', 7)->nullable();
            $table->boolean('show_school_logo')->default(false);
            $table->boolean('show_head_signature')->default(false);
            $table->boolean('show_stamp')->default(false);

            // Modern/Formal marksheet extras (off for old rows; the template form ticks them for a new template)
            $table->boolean('show_subject_details')->default(false); // Pass Marks, %, Grade, Pass/Fail per subject
            $table->boolean('show_summary_boxes')->default(false);   // Percentage, Grade, Result, Position boxes
            $table->boolean('show_grade_scale')->default(false);     // grade scale table (marks_grades of the exam)
            $table->boolean('show_parent_sign')->default(false);     // 4th signature slot "Parent / Guardian"

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marksheet_templates');
    }
};
