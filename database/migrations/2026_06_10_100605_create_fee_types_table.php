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
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            // 'after' method yahan hata dein, yeh automatically last mein add ho jayega
            $table->string('frequency')->default('monthly');
            $table->boolean('is_discountable')->default(false);
            // 1 = sirf Assign Fee se lagti hai (jaise Tuition), class-wide generate nahi hoti
            $table->boolean('is_per_student')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_types');
    }
};
