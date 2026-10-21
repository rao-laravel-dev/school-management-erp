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
