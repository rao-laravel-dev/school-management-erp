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
        Schema::create('print_report_cards', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('enrollment_id')->constrained('enrollments');
            $table->foreignId('exam_id')->constrained('exams');
            $table->foreignId('template_id')->constrained('report_card_templates');
 
            $table->text('class_teacher_remark')->nullable();
 
            $table->foreignId('issued_by')->nullable()->constrained('users');
            $table->timestamp('issued_at')->nullable();
 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_report_cards');
    }
};
