<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Existing rows current year ki maani jayengi: is liye exactly ek current year zaroori. Data khud theek nahi karte.
        $current = DB::table('academic_years')->where('is_current', 1)->pluck('id');

        if ($current->count() !== 1) {
            throw new RuntimeException("academic_years: exactly one is_current=1 row required, found {$current->count()}. Pehle ek session current mark karein.");
        }

        $yearId = $current->first();

        Schema::table('class_timetables', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->after('id');
        });

        // Purana saara timetable current year ka
        DB::table('class_timetables')->update(['academic_year_id' => $yearId]);

        Schema::table('class_timetables', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable(false)->change();

            // History hai (lesson plans link): saal delete hone par timetable na mite
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->restrictOnDelete();

            // getData / save queries: year + class + section + day
            $table->index(['academic_year_id', 'school_class_id', 'section_id', 'day'], 'class_timetables_year_class_section_day_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_timetables', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropIndex('class_timetables_year_class_section_day_index');
            $table->dropColumn('academic_year_id');
        });
    }
};
