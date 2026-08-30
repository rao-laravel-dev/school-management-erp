<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // 🔗 Link to Users Table for Student Login
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // 👨‍👦 Link to Parent Profiles Table
            $table->foreignId('parent_id')->nullable()->constrained('parent_profiles')->nullOnDelete();

            // 🎓 Admission & Core Info
            $table->string('admission_no')->unique();
            $table->date('admission_date');

            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();

            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('date_of_birth');

            // 🧬 Student Classification
            $table->foreignId('category_id')->nullable()->constrained('student_categories')->nullOnDelete();

            $table->foreignId('house_id')->nullable()->constrained('student_houses')->nullOnDelete();

            $table->string('religion')->nullable();
            $table->string('caste')->nullable();
            $table->string('blood_group')->nullable();

            // 📏 Physical & Medical Info
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->date('measurement_date')->nullable();
            $table->text('medical_history')->nullable();

            // 📸 Student Photo
            $table->string('photo')->nullable();

            // 📍 Address
            $table->text('address')->nullable();

            // ⚙️ Student Status
            // 0 = Inactive
            // 1 = Active
            // 2 = Pending Approval
            // 3 = Passed Out
            $table->tinyInteger('status')->default(2);

            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
