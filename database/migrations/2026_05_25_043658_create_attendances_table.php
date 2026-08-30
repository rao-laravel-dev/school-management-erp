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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('school_class')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->date('attendance_date');
            $table->tinyInteger('status')->default(0)->comment('0:Absent, 1:Present, 2:Late, 3:Leave');
            $table->time('time_out')->nullable();
            $table->text('remarks')->nullable();
            
            // New columns added without after()
            $table->enum('marked_via', ['manual', 'device'])->default('manual');
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('device_id')->nullable()->comment('Scanner/camera device identifier');
            $table->timestamp('scanned_at')->nullable();

            $table->timestamps();

            // Unique constraint taake ek din mein ek student ki entry ek hi baar ho
            $table->unique(['enrollment_id', 'attendance_date'], 'enrollment_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
