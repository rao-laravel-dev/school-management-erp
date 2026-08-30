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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name'); // E.g., Meezan Bank, Easypaisa
            $table->enum('bank_type', ['commercial', 'microfinance_wallet', 'cash'])->default('commercial');
            $table->string('account_title'); // E.g., SmartSchool Official
            $table->string('account_number'); // Account No ya Mobile No
            $table->string('iban')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('branch_code')->nullable();
            $table->decimal('current_balance', 15, 2)->default(0.00); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
