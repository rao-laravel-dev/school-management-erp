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
        Schema::create('report_card_templates', function (Blueprint $table) {
            $table->id();
 
            $table->string('template_name')->index(); // for DataTable search
            $table->text('body_text')->nullable();     // bracket placeholders like marksheet_templates
            $table->text('footer_text')->nullable();
 
            // School name, logo, address, phone, email are NOT stored here —
            // pulled live from the Site Settings module at print/render time
            // (this table only keeps design elements specific to THIS document)
            // Uploaded via ImageService, folder 'uploads/report_card_template' — filename only stored
            $table->string('header_image')->nullable();
            $table->string('left_sign')->nullable();
            $table->string('middle_sign')->nullable();
            $table->string('right_sign')->nullable();
            $table->string('background_image')->nullable();
 
            // Student-info field toggles (same set as marksheet_templates)
            $table->boolean('show_name')->default(true);
            $table->boolean('show_father_name')->default(true);
            $table->boolean('show_mother_name')->default(false);
            $table->boolean('show_admission_no')->default(true);
            $table->boolean('show_roll_number')->default(true);
            $table->boolean('show_photo')->default(true);
            $table->boolean('show_class')->default(true);
            $table->boolean('show_section')->default(true);
            $table->boolean('show_dob')->default(false);
 
            // Report Card-specific section toggles
            $table->boolean('show_attendance_summary')->default(true);
            $table->boolean('show_co_curricular')->default(true);
            $table->boolean('show_behavioral')->default(true);
            $table->boolean('show_class_teacher_remark')->default(true);
 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_card_templates');
    }
};
