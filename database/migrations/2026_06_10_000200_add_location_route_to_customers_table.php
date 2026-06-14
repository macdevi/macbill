<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }

            if (!Schema::hasColumn('customers', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }

            if (!Schema::hasColumn('customers', 'cable_path_json')) {
                $table->text('cable_path_json')->nullable();
            }

            if (!Schema::hasColumn('customers', 'cable_distance_m')) {
                $table->integer('cable_distance_m')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'latitude')) {
                $table->dropColumn('latitude');
            }

            if (Schema::hasColumn('customers', 'longitude')) {
                $table->dropColumn('longitude');
            }

            if (Schema::hasColumn('customers', 'cable_path_json')) {
                $table->dropColumn('cable_path_json');
            }

            if (Schema::hasColumn('customers', 'cable_distance_m')) {
                $table->dropColumn('cable_distance_m');
            }
        });
    }
};
