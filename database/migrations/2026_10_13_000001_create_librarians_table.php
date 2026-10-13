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
        // Librarian profile (teachers/accountants jaisa). Bank = staff_bank_details, salary = staff_salaries, status = users.status
        Schema::create('librarians', function (Blueprint $table) {
            $table->id();

            // Ek user ki sirf ek librarian profile; user sirf force delete (history guard ke baad) mein delete hota hai
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            // Identity & Profile
            $table->string('librarian_id', 100)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('email');
            $table->string('cnic', 20)->nullable()->unique();
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
            $table->string('contract_type')->nullable();
            $table->string('work_shift')->nullable();
            $table->string('photo')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('librarians');
    }
};
