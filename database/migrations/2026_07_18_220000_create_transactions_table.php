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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('student_fee_id')->nullable()->constrained('student_fees')->nullOnDelete();
            $table->foreignId('salary_slip_id')->nullable()->constrained('salary_slips')->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();

            $table->unsignedBigInteger('payment_group_id')->nullable()->index();
            $table->enum('type', ['income', 'expense']);
            $table->enum('category', ['fee', 'salary', 'expense', 'refund', 'advance'])->default('expense');
            $table->decimal('amount', 10, 2);
            $table->decimal('discount', 10, 2)->default(0); // 🔥 naya column
            $table->decimal('fine', 10, 2)->default(0);      // 🔥 naya column
            $table->string('payment_method')->default('cash');
            $table->string('reference_no')->nullable();
            $table->date('transaction_date');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
