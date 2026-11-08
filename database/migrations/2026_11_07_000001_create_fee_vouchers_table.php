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
        Schema::create('fee_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_no')->unique();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->char('period', 7); // Y-m
            $table->json('student_fee_ids')->nullable();
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('arrears', 10, 2)->default(0);
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->decimal('amount_before_due', 10, 2)->default(0);
            $table->decimal('amount_after_due', 10, 2)->default(0);
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete(); // null = 2 copies
            $table->enum('status', ['unpaid', 'paid', 'cancelled'])->default('unpaid')->index();
            $table->timestamp('paid_at')->nullable();
            // 1Bill (abhi sirf columns, API baad mein)
            $table->string('consumer_no')->nullable()->index();
            $table->string('onebill_invoice_no')->nullable()->unique();
            $table->string('bank_reference')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Ek student ka ek mahine ka ek hi voucher
            $table->unique(['student_id', 'period'], 'fee_vouchers_student_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_vouchers');
    }
};
