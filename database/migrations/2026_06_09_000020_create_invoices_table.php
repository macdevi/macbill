<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('package_id')->nullable();
                $table->string('period', 7);
                $table->date('due_date');
                $table->integer('amount')->default(0);
                $table->integer('paid_amount')->default(0);
                $table->string('status')->default('Belum Bayar');
                $table->dateTime('paid_at')->nullable();
                $table->string('payment_method')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['customer_id', 'period']);
                $table->index(['period', 'status']);
                $table->index(['due_date', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
