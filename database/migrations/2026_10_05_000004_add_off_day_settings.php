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
        Schema::table('academic_years', function (Blueprint $table) {
            // Weekly off din (JSON array), e.g. ["Sunday"]
            $table->json('weekly_off_days')->nullable()->after('end_date');
        });

        // Existing years ke liye default Sunday (MySQL JSON column default nahi leta)
        DB::table('academic_years')->update(['weekly_off_days' => json_encode(['Sunday'])]);

        Schema::table('event_types', function (Blueprint $table) {
            // 1 = is type ki calendar dates timetable mein off (Eid, Ashura, leaves...)
            $table->boolean('is_off_day')->default(false)->after('color');
        });

        // Existing 'Holiday' type timetable mein off maana jaye
        DB::table('event_types')->where('name', 'Holiday')->update(['is_off_day' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn('weekly_off_days');
        });

        Schema::table('event_types', function (Blueprint $table) {
            $table->dropColumn('is_off_day');
        });
    }
};
