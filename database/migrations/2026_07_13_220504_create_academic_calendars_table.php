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
        Schema::create('academic_calendars', function (Blueprint $table) {
            $table->id();

            // Academic Year
            $table->foreignId('academic_year_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Event Information
            $table->string('title');
            $table->text('description')->nullable();

            // Event Date
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();

            // Event Type (FK ab yahan)
            $table->foreignId('event_type_id')
                ->constrained('event_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Calendar Color
            $table->string('color', 20)->default('#0d6efd');

            // FullCalendar
            $table->boolean('all_day')->default(true);

            // Status
            $table->boolean('status')->default(true);

            // Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index(['academic_year_id', 'event_type_id']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_calendars');
    }
};
