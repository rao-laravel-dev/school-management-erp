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
        Schema::create('id_card_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('background_image')->nullable();
            $table->string('logo')->nullable();
            $table->string('signature')->nullable();
            $table->string('school_name');
            $table->string('address_phone_email')->nullable();
            $table->string('header_color')->nullable();
            $table->enum('design_type', ['horizontal', 'vertical'])->default('horizontal');

            $table->boolean('show_admission_no')->default(true);
            $table->boolean('show_student_name')->default(true);
            $table->boolean('show_class')->default(true);
            $table->boolean('show_father_name')->default(true);
            $table->boolean('show_mother_name')->default(false);
            $table->boolean('show_address')->default(false);
            $table->boolean('show_phone')->default(false);
            $table->boolean('show_dob')->default(true);
            $table->boolean('show_blood_group')->default(true);
            $table->boolean('show_roll_no')->default(true);
            $table->boolean('show_house')->default(false);
            $table->boolean('show_qr_code')->default(true);
            $table->boolean('show_barcode')->default(false);

            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('id_card_templates');
    }
};
