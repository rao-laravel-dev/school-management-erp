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
        Schema::table('class_timetables', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('teacher_id')
                ->constrained('rooms')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_timetables', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
        });
    }
};
