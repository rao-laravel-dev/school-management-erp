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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('id_card')->nullable();
            $table->integer('no_of_person')->default(1);
            $table->date('date');
            $table->time('in_time');
            $table->time('out_time')->nullable();
            
            // Purpose relation
            $table->foreignId('purpose_id')->constrained('purposes')->onDelete('cascade');
            
            // New Meeting With Fields
            $table->string('meeting_with_type')->nullable(); // 'student' or 'staff'
            $table->unsignedBigInteger('meeting_with_id')->nullable(); // ID of the person
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
