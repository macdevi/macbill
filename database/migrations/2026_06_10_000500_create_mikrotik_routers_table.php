<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mikrotik_routers')) {
            Schema::create('mikrotik_routers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('host');
                $table->unsignedInteger('api_port')->default(8728);
                $table->string('username');
                $table->text('api_password')->nullable();
                $table->boolean('use_ssl')->default(false);
                $table->string('status')->default('active');
                $table->text('notes')->nullable();
                $table->string('last_test_status')->nullable();
                $table->text('last_test_message')->nullable();
                $table->timestamp('last_test_at')->nullable();
                $table->timestamps();

                $table->index(['status']);
                $table->index(['host']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_routers');
    }
};
