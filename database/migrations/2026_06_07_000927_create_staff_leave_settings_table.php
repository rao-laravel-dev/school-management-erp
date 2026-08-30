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
        Schema::create('staff_leave_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('leave_type');
            $table->integer('total_days')->default(0);
            
            // Yahan role_id add kar diya gaya hai
            $table->unsignedBigInteger('role_id')->nullable(); 
            
            $table->timestamps();

            // Unique index: Ek user aur ek leave_type ka record sirf ek baar banayega
            $table->unique(['user_id', 'leave_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_leave_settings');
    }
};
