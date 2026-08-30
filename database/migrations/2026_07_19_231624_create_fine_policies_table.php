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
        Schema::create('fine_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                          // "Late Tuition Fine"
            $table->foreignId('fee_type_id')->nullable()->constrained('fee_types')->nullOnDelete(); // null = sab fees par
            $table->unsignedInteger('grace_days')->default(0);
            $table->enum('fine_type', ['fixed', 'percentage', 'per_day']);
            $table->decimal('fine_value', 10, 2);
            $table->decimal('max_fine', 10, 2)->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fine_policies');
    }
};
