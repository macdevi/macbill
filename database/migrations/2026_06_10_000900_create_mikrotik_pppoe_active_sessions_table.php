<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mikrotik_pppoe_active_sessions')) {
            Schema::create('mikrotik_pppoe_active_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mikrotik_router_id')->constrained('mikrotik_routers')->cascadeOnDelete();
                $table->string('mikrotik_id')->nullable();
                $table->string('name');
                $table->string('service')->nullable();
                $table->string('caller_id')->nullable();
                $table->string('address')->nullable();
                $table->string('uptime')->nullable();
                $table->string('encoding')->nullable();
                $table->text('raw_json')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();

                $table->unique(['mikrotik_router_id', 'name']);
                $table->index(['mikrotik_router_id']);
                $table->index(['name']);
            });
        }

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'pppoe_online_status')) {
                $table->string('pppoe_online_status')->default('Unknown')->after('mikrotik_sync_message');
            }

            if (!Schema::hasColumn('customers', 'pppoe_online_at')) {
                $table->timestamp('pppoe_online_at')->nullable()->after('pppoe_online_status');
            }

            if (!Schema::hasColumn('customers', 'pppoe_last_seen_at')) {
                $table->timestamp('pppoe_last_seen_at')->nullable()->after('pppoe_online_at');
            }

            if (!Schema::hasColumn('customers', 'pppoe_remote_address')) {
                $table->string('pppoe_remote_address')->nullable()->after('pppoe_last_seen_at');
            }

            if (!Schema::hasColumn('customers', 'pppoe_caller_id')) {
                $table->string('pppoe_caller_id')->nullable()->after('pppoe_remote_address');
            }

            if (!Schema::hasColumn('customers', 'pppoe_uptime')) {
                $table->string('pppoe_uptime')->nullable()->after('pppoe_caller_id');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_pppoe_active_sessions');
    }
};
