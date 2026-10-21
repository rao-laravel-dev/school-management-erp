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
        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('fee_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_structure_id')->nullable()->constrained()->onDelete('cascade');
            // FK create_fee_assignments mein hai, kyun ke fee_assignments table is se baad mein banta hai
            $table->unsignedBigInteger('fee_assignment_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('due_date');
            // Y-m: auto generate ka mahina. Purani rows (manual Generate / admission) mein NULL rehta hai
            $table->char('period', 7)->nullable();
            $table->string('status')->default('unpaid'); // unpaid, partial, paid, excluded
            $table->foreignId('excluded_by')->nullable()->constrained('users')->nullOnDelete(); // 🔥 naya
            $table->string('excluded_reason')->nullable(); // 🔥 naya
            $table->timestamps();

            $table->index('due_date');
            $table->index('status');
            $table->unique(['student_id', 'fee_structure_id', 'period'], 'student_fees_student_structure_period_unique');
            // Structure wali rows (fee_assignment_id NULL) is index mein nahi aati
            $table->unique(['student_id', 'fee_assignment_id', 'period'], 'student_fees_student_assignment_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
