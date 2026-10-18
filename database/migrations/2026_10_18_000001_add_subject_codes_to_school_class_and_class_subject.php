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
        Schema::table('school_class', function (Blueprint $table) {
            // Admin-entered prefix for class-wise subject codes (NR, K1, 01, 10)
            $table->string('subject_prefix', 4)->nullable()->unique()->after('class_code');
        });

        Schema::table('class_subject', function (Blueprint $table) {
            // Class-wise subject code = subject_prefix + 2-digit serial. Same subject in two groups of one class shares one code,
            // isliye unique nahi, sirf index (uniqueness app level par).
            $table->string('subject_code', 10)->nullable()->after('group_id');
            $table->index(['class_id', 'subject_code'], 'class_subject_class_code_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_subject', function (Blueprint $table) {
            $table->dropIndex('class_subject_class_code_index');
            $table->dropColumn('subject_code');
        });

        Schema::table('school_class', function (Blueprint $table) {
            $table->dropUnique(['subject_prefix']);
            $table->dropColumn('subject_prefix');
        });
    }
};
