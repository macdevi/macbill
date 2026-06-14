<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mikrotik_pppoe_secrets')) {
            Schema::create('mikrotik_pppoe_secrets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mikrotik_router_id')->constrained('mikrotik_routers')->cascadeOnDelete();
                $table->string('mikrotik_id')->nullable();
                $table->string('name');
                $table->text('password')->nullable();
                $table->string('service')->nullable();
                $table->string('profile')->nullable();
                $table->string('local_address')->nullable();
                $table->string('remote_address')->nullable();
                $table->string('disabled')->nullable();
                $table->text('comment')->nullable();
                $table->text('raw_json')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();

                $table->unique(['mikrotik_router_id', 'name']);
                $table->index(['mikrotik_router_id']);
                $table->index(['profile']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_pppoe_secrets');
    }
};
