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
        Schema::create('staff_id_card_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('background_image')->nullable();
            $table->string('logo')->nullable();
            $table->string('signature')->nullable();
            $table->string('school_name');
            $table->string('address_phone_email')->nullable();
            $table->string('header_color')->nullable();
            $table->enum('design_type', ['horizontal', 'vertical'])->default('horizontal');

            $table->boolean('show_staff_name')->default(true);
            $table->boolean('show_staff_id')->default(true);
            $table->boolean('show_designation')->default(true);
            $table->boolean('show_department')->default(true);
            $table->boolean('show_father_name')->default(false);
            $table->boolean('show_mother_name')->default(false);
            $table->boolean('show_date_of_joining')->default(false);
            $table->boolean('show_current_address')->default(false);
            $table->boolean('show_phone')->default(false);
            $table->boolean('show_dob')->default(false);
            $table->boolean('show_qr_code')->default(false);
            $table->boolean('show_barcode')->default(true);

            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_id_card_templates');
    }
};
