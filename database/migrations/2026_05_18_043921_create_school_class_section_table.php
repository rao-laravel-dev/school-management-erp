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
        Schema::create('school_class_section', function (Blueprint $table) {
            $table->id();

            // Foreign Key 1: Link with school_classes table
            $table->foreignId('school_class_id')
                ->constrained('school_class')
                ->onDelete('cascade');

            // Foreign Key 2: Link with sections table
            $table->foreignId('section_id')
                ->constrained('sections')
                ->onDelete('cascade');

            // Mapping status control (1 = Active mapping, 0 = Inactive mapping)
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_class_section');
    }
};
