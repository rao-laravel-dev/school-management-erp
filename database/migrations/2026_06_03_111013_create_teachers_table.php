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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            // Relationships (Proper Foreign Keys)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Identity & Profile
            $table->string('teacher_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('email');
            $table->string('cnic', 20)->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('marital_status')->nullable();

            // Contact & Location
            $table->string('phone')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('emergency_relation')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->text('address')->nullable();
            $table->text('permanent_address')->nullable();

            // Professional Info
            $table->string('qualification')->nullable();
            $table->string('work_experience')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('photo')->nullable();
            $table->string('contract_type')->nullable();
            $table->string('work_shift')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
