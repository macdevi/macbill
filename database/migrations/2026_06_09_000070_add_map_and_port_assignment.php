<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('odps', function (Blueprint $table) {
            if (!Schema::hasColumn('odps', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }

            if (!Schema::hasColumn('odps', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'port_number')) {
                $table->integer('port_number')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('odps', function (Blueprint $table) {
            if (Schema::hasColumn('odps', 'latitude')) {
                $table->dropColumn('latitude');
            }

            if (Schema::hasColumn('odps', 'longitude')) {
                $table->dropColumn('longitude');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'port_number')) {
                $table->dropColumn('port_number');
            }
        });
    }
};
