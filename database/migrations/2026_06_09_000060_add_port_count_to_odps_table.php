<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('odps', function (Blueprint $table) {
            if (!Schema::hasColumn('odps', 'port_count')) {
                $table->integer('port_count')->default(0)->after('location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('odps', function (Blueprint $table) {
            if (Schema::hasColumn('odps', 'port_count')) {
                $table->dropColumn('port_count');
            }
        });
    }
};
