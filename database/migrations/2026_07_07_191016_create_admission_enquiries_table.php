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
        Schema::create('admission_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->date('date');
            $table->date('next_follow_up_date')->nullable();

            // Foreign Keys (Settings se connect krne k liye)
            $table->foreignId('source_id')->constrained('sources')->onDelete('cascade');
            $table->foreignId('purpose_id')->constrained('purposes')->onDelete('cascade');

            // Kaunsa staff handle kr rha ha
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            $table->string('status')->default('active'); // active, passive, resolved
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_enquiries');
    }
};
