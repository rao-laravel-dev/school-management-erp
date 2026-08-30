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
        Schema::create('discount_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                          // "Sibling 2nd Child", "Staff Child Discount"
            $table->enum('policy_type', ['category', 'sibling', 'other']);    // renamed from 'category' to avoid clash
            $table->foreignId('category_id')->nullable()->constrained('student_categories')->nullOnDelete(); // sirf policy_type = 'category' ke liye
            $table->unsignedTinyInteger('trigger_value')->nullable();         // sirf policy_type = 'sibling' ke liye: 2, 3...
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 10, 2);
            $table->foreignId('fee_type_id')->nullable()->constrained('fee_types')->nullOnDelete(); // null = sab fees par
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_policies');
    }
};
