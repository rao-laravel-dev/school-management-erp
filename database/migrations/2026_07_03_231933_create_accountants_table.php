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
        Schema::create('accountants', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('accountant_id', 100)->unique();

            $table->string('first_name');
            $table->string('last_name');

            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();

            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('cnic', 20)->nullable();

            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('marital_status')->nullable();

            $table->text('address')->nullable();
            $table->text('permanent_address')->nullable();

            $table->string('emergency_name')->nullable();
            $table->string('emergency_relation')->nullable();
            $table->string('emergency_phone')->nullable();

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
        Schema::dropIfExists('accountants');
    }
};
