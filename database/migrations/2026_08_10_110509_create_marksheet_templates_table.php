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
