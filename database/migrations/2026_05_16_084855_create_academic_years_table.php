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
    Schema::create('academic_years', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique(); // Misaal ke tor par: "2026-2027" ya "2026"
        $table->date('start_date')->nullable(); // Session kab shuru hua
        $table->date('end_date')->nullable();   // Session kab khatam hoga
        
        // 1 = Current Active Session, 0 = Past/Future Session
        // Poore table mein sirf AIK record hamesha 1 ho sakta hai
        $table->tinyInteger('is_current')->default(0); 
        
        $table->tinyInteger('status')->default(1); // 1 = Active, 0 = Inactive
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
