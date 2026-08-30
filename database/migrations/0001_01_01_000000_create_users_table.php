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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // 🪪 Login Identity: Yahan Student ka Admission No ya Parent ka CNIC save hoga
            $table->string('username')->unique();

            // 📧 Nullable & Unique (Students ke liye null safe, par agar email aaye to unique ho)
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            
            $table->string('password');

            // 📱 General Profile Fields for User
            $table->string('photo')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('gender')->nullable();

            // ⚙️ Status: 0 = Inactive/Blocked, 1 = Active, 2 = Pending Admin Approval
            $table->tinyInteger('status')->default(2);

            $table->rememberToken();
            $table->timestamps();

            // 🗑️ Soft Delete Support
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};