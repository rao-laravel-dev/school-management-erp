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
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('school_class_id')->nullable()->after('type')
                ->constrained('school_class')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->after('school_class_id')
                ->constrained('sections')->nullOnDelete();

            // Ek class+section ka sirf ek room (NULL wali rows, jaise Lab/Hall, duplicate nahi maani jatin)
            $table->unique(['school_class_id', 'section_id'], 'rooms_class_section_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropUnique('rooms_class_section_unique');
            $table->dropConstrainedForeignId('section_id');
            $table->dropConstrainedForeignId('school_class_id');
        });
    }
};
