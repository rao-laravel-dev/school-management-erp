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
        Schema::create('staff_leave_applications', function (Blueprint $table) {
            $table->id();

            // Generic Staff Reference (Koi bhi staff, teacher, accountant etc.)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Form Fields
            $table->date('apply_date');
            $table->string('leave_type'); 
            $table->date('from_date');
            $table->date('to_date');
            $table->string('leave_duration'); // full, first_half, second_half
            $table->decimal('total_days', 8, 2); // decimal rakha hai taake 0.5 days save ho sakein
            $table->text('reason')->nullable();
            $table->string('document')->nullable(); // File path

            // Admin Management Fields
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_comment')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            
            // Indexing for faster search (Performance ke liye)
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_leave_applications');
    }
};
