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
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_type_id')->constrained('fee_types')->onDelete('cascade');
            $table->foreignId('school_class_id')->nullable()->constrained('school_class')->onDelete('cascade');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->string('last_generated_period')->nullable();   // 🔥 naya column
            $table->timestamp('last_generated_at')->nullable();     // 🔥 naya column
            $table->timestamps();
        });

        // student_fees.fee_structure_id FK yahan hai: student_fees (2026_06_10) is table se pehle banta hai
        Schema::table('student_fees', function (Blueprint $table) {
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropForeign(['fee_structure_id']);
        });

        Schema::dropIfExists('fee_structures');
    }
};
