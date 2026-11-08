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
        Schema::create('billing_settings', function (Blueprint $table) {
            $table->id();
            // Ek academic year ki ek hi setting row
            $table->foreignId('academic_year_id')->unique()->constrained('academic_years')->onDelete('cascade');
            $table->unsignedTinyInteger('fee_generate_day')->default(1);
            $table->unsignedTinyInteger('fee_due_day')->default(10);
            $table->unsignedTinyInteger('salary_generate_day')->default(1);
            $table->boolean('auto_fees')->default(false);
            $table->boolean('auto_salary')->default(false);
            $table->char('fees_from_month', 7)->nullable();   // Y-m, catch-up isse pehle nahi jata
            $table->char('salary_from_month', 7)->nullable(); // Y-m
            // Fee Voucher
            $table->decimal('late_fee_amount', 10, 2)->default(0); // 0 = late fee nahi
            $table->foreignId('voucher_bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete(); // null = 2 copies
            $table->text('voucher_note')->nullable();
            $table->foreignId('scholarship_category_id')->nullable()->constrained('student_categories')->nullOnDelete();
            // 1Bill (sirf storage; credentials model mein encrypted cast)
            $table->boolean('onebill_enabled')->default(false);
            $table->string('onebill_bank')->nullable();
            $table->string('onebill_biller_code')->nullable();
            $table->text('onebill_username')->nullable();
            $table->text('onebill_password')->nullable();
            $table->text('onebill_api_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_settings');
    }
};
