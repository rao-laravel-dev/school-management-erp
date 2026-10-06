<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // In dono ka teacher_id galti se users ko point karta tha, value teachers.id hai
    private const TABLES = ['class_timetables', 'lesson_plans'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Orphans hon to ruk jao. Data khud delete/update nahi karte.
        foreach (self::TABLES as $tableName) {
            $orphans = DB::table($tableName . ' as x')
                ->leftJoin('teachers as t', 't.id', '=', 'x.teacher_id')
                ->whereNull('t.id')
                ->count();

            if ($orphans > 0) {
                throw new RuntimeException("{$tableName}: {$orphans} rows ka teacher_id teachers.id mein nahi. Pehle orphans theek karein.");
            }
        }

        foreach (self::TABLES as $tableName) {
            $this->dropTeacherForeignKey($tableName);

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('teacher_id')->references('id')->on('teachers')->restrictOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sirf naya FK hatao. Purana users wala cascade FK wapas nahi lagate (wo galat aur khatarnak tha).
        foreach (self::TABLES as $tableName) {
            $this->dropTeacherForeignKey($tableName);
        }
    }

    // teacher_id par jo bhi FK ho (naam se independent) hatao, taake up/down dobara chal sakein
    private function dropTeacherForeignKey(string $tableName): void
    {
        foreach (Schema::getForeignKeys($tableName) as $fk) {
            if ($fk['columns'] === ['teacher_id']) {
                Schema::table($tableName, function (Blueprint $table) use ($fk) {
                    $table->dropForeign($fk['name']);
                });
            }
        }
    }
};
