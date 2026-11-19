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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('logo')->nullable();
            $table->string('icon')->nullable();
            $table->string('principal_signature')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('registration_no')->nullable();
            $table->string('established_year')->nullable();
            $table->string('admission_prefix', 10)->nullable()->default('SCH'); // New Field Added
            $table->string('header_note')->nullable();
            $table->string('footer_note')->nullable();
            // T340a: tagline + theme (defaults = today's look); login_* columns are used by T340b
            $table->string('tagline', 150)->nullable();
            $table->string('theme_palette', 30)->default('default');
            $table->string('primary_color', 7)->default('#008cff');
            $table->string('accent_color', 7)->default('#ffb020');
            $table->string('font_family', 40)->default('roboto');
            $table->string('sidebar_style', 10)->default('light');
            $table->string('login_heading', 150)->nullable();
            $table->string('login_message', 255)->nullable();
            $table->string('login_background')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
