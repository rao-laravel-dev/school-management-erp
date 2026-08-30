<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_profiles', function (Blueprint $table) {
            $table->id();

            // 🔗 Link to Users Table for Login Authorization
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // 👨‍👩‍👧 Parent / Guardian Details
            $table->string('father_name');
            $table->string('father_phone');
            $table->string('mother_name')->nullable();
            $table->string('mother_phone')->nullable();

            // 🛡️ Guardian Logic
            $table->enum('is_guardian', ['father', 'mother', 'other'])->default('father');
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relation')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_email')->nullable();
            $table->text('guardian_address')->nullable();

            // 🪪 CNIC / Identification & Documents
            $table->string('father_cnic')->nullable();
            $table->string('father_cnic_front')->nullable();
            $table->string('father_cnic_back')->nullable();

            $table->string('mother_cnic')->nullable();
            $table->string('mother_cnic_front')->nullable();
            $table->string('mother_cnic_back')->nullable();

            // 📸 Photos
            $table->string('father_photo')->nullable();
            $table->string('mother_photo')->nullable();
            $table->string('guardian_photo')->nullable();

            $table->timestamps();
            
            // 🗑️ Soft Delete Support for Parents
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_profiles');
    }
};