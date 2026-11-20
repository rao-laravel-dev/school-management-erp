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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_no', 30);
            $table->string('type', 40); // built-in key ya custom key
            $table->unsignedSmallInteger('serial_year');
            $table->unsignedInteger('serial');
            // Certificate record history hai, student delete se gayab nahi hona chahiye
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->string('title', 150);
            $table->text('body'); // final text (placeholders replace ho chuke), reprint par purani class/session na badle
            $table->json('fields')->nullable(); // leaving_date, reason, conduct, exam, result ... + template
            $table->string('design', 20)->default('classic');
            $table->string('orientation', 10)->default('portrait');
            $table->date('issue_date');
            $table->string('status', 10)->default('issued'); // issued / cancelled (delete nahi)
            $table->string('cancel_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('certificate_no', 'certificates_certificate_no_unique');
            // Ek type + saal mein serial dobara nahi (race ka akhri guard)
            $table->unique(['type', 'serial_year', 'serial'], 'certificates_type_year_serial_unique');
            $table->index(['status', 'issue_date'], 'certificates_status_issue_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
