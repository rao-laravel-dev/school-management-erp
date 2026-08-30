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
        Schema::create('sections', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // A, B, Blue
        $table->string('section_code')->unique(); // 🛠️ UPDATED: Code ab poori table mein unique hoga (e.g., SEC-A, SEC-B)
        $table->integer('capacity')->nullable();
        $table->text('description')->nullable();
        $table->boolean('status')->default(1); 
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
