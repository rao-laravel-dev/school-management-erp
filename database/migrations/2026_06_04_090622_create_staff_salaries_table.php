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
    Schema::create('staff_salaries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->decimal('basic_salary', 10, 2); 
    $table->decimal('allowance', 10, 2)->default(0); 
    $table->decimal('deduction', 10, 2)->default(0);
    $table->decimal('advance_balance', 10, 2)->default(0); // Advance salary track krny k liye
    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_salaries');
    }
};
