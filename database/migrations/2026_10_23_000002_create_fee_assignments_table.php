<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('fee_type_id')->constrained('fee_types')->restrictOnDelete();
            $table->string('kind', 20);                    // one_time, recurring_monthly
            $table->string('title', 150);
            $table->decimal('amount', 12, 2);
            $table->date('due_date')->nullable();          // sirf one_time
            $table->char('start_month', 7)->nullable();    // Y-m, sirf recurring_monthly
            $table->char('end_month', 7)->nullable();      // Y-m, optional
            $table->string('target_type', 20);             // school, classes, section, students
            $table->string('target_label')->nullable();
            $table->json('student_ids');                   // assignment ke waqt ke active students; history student_fees mein
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['academic_year_id', 'kind'], 'fee_assignments_year_kind_index');
        });

        Schema::table('student_fees', function (Blueprint $table) {
            // nullOnDelete: assignment hatne se payment wali rows kabhi delete nahi hoti
            $table->foreign('fee_assignment_id')->references('id')->on('fee_assignments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropForeign(['fee_assignment_id']);
        });

        Schema::dropIfExists('fee_assignments');
    }
};
