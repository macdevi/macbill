<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('collector_id')->nullable()->constrained('users')->nullOnDelete();
                $table->integer('amount')->default(0);
                $table->string('method')->default('cash');
                $table->text('notes')->nullable();
                $table->dateTime('paid_at');
                $table->timestamps();

                $table->index(['customer_id', 'paid_at']);
                $table->index(['collector_id', 'paid_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
