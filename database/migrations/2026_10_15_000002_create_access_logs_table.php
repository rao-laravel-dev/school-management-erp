<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Audit log: superadmin ne kis user/role ko kab block/unblock kiya
    public function up(): void
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id'); // users.id (superadmin)
            $table->string('target_type', 10);      // user | role
            $table->unsignedBigInteger('target_id');
            $table->string('target_name')->nullable(); // name snapshot (user/role baad mein delete ho to bhi log readable)
            $table->string('action', 20);           // activate | deactivate | enable | disable
            $table->string('old_value', 20)->nullable();
            $table->string('new_value', 20)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['target_type', 'target_id']);
            $table->index('actor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};
