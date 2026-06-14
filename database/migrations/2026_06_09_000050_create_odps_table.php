<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('odps')) {
            Schema::create('odps', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('location')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'odp_id')) {
                $table->foreignId('odp_id')->nullable()->after('address')->constrained('odps')->nullOnDelete();
            }

            if (!Schema::hasColumn('customers', 'odp')) {
                $table->string('odp')->nullable()->after('odp_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'odp_id')) {
                $table->dropConstrainedForeignId('odp_id');
            }
        });

        Schema::dropIfExists('odps');
    }
};
