<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'mikrotik_router_id')) {
                $table->foreignId('mikrotik_router_id')->nullable()->after('port_number')->constrained('mikrotik_routers')->nullOnDelete();
            }

            if (!Schema::hasColumn('customers', 'mikrotik_pppoe_profile_id')) {
                $table->foreignId('mikrotik_pppoe_profile_id')->nullable()->after('mikrotik_router_id')->constrained('mikrotik_pppoe_profiles')->nullOnDelete();
            }

            if (!Schema::hasColumn('customers', 'pppoe_username')) {
                $table->string('pppoe_username')->nullable()->after('mikrotik_pppoe_profile_id');
            }

            if (!Schema::hasColumn('customers', 'pppoe_password')) {
                $table->text('pppoe_password')->nullable()->after('pppoe_username');
            }

            if (!Schema::hasColumn('customers', 'mikrotik_sync_status')) {
                $table->string('mikrotik_sync_status')->default('Belum Sync')->after('pppoe_password');
            }

            if (!Schema::hasColumn('customers', 'mikrotik_synced_at')) {
                $table->timestamp('mikrotik_synced_at')->nullable()->after('mikrotik_sync_status');
            }

            if (!Schema::hasColumn('customers', 'mikrotik_sync_message')) {
                $table->text('mikrotik_sync_message')->nullable()->after('mikrotik_synced_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            foreach ([
                'mikrotik_router_id',
                'mikrotik_pppoe_profile_id',
                'pppoe_username',
                'pppoe_password',
                'mikrotik_sync_status',
                'mikrotik_synced_at',
                'mikrotik_sync_message',
            ] as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
