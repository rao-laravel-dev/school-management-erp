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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_by'); // Name of person lodging complaint
            $table->string('phone');
            $table->string('email')->nullable();
            $table->date('date');
            $table->text('description');
            $table->text('action_taken')->nullable();

            // Foreign Keys
            $table->foreignId('complaint_type_id')->constrained('complaint_types')->onDelete('cascade');
            $table->foreignId('source_id')->nullable()->constrained('sources')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            $table->string('document')->nullable(); // For file attachment proof
            $table->string('status')->default('pending'); // pending, in_progress, resolved, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};