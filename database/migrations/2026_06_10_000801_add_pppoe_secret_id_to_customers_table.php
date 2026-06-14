<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'mikrotik_pppoe_secret_id')) {
                $table->foreignId('mikrotik_pppoe_secret_id')->nullable()->after('mikrotik_pppoe_profile_id')->constrained('mikrotik_pppoe_secrets')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'mikrotik_pppoe_secret_id')) {
                $table->dropColumn('mikrotik_pppoe_secret_id');
            }
        });
    }
};
