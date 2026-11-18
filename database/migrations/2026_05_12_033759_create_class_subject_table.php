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
        Schema::create('class_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('school_class')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();

            // Group nullable hai kyunke Class 1-8 mein groups nahi hote
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();

            // Class-wise subject code = subject_prefix + 2-digit serial. Same subject in two groups of one class shares one code,
            // isliye unique nahi, sirf index (uniqueness app level par).
            $table->string('subject_code', 10)->nullable();

            // Marks ki logic yahan pivot mein flexible rakhi hai
            $table->integer('full_marks')->default(100);
            $table->integer('pass_marks')->default(33);

            $table->timestamps();

            $table->index(['class_id', 'subject_code'], 'class_subject_class_code_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_subject');
    }
};
