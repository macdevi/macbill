<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoices')) {
            return;
        }

        $this->assertNoDuplicateInvoices();
        $this->assertNoDuplicatePayments();

        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS invoices_customer_period_unique ON invoices (customer_id, period)');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS invoices_invoice_number_unique ON invoices (invoice_number)');

        if (Schema::hasTable('payments')) {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS payments_invoice_id_unique ON payments (invoice_id)');
        }
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS payments_invoice_id_unique');
        DB::statement('DROP INDEX IF EXISTS invoices_invoice_number_unique');
        DB::statement('DROP INDEX IF EXISTS invoices_customer_period_unique');
    }

    private function assertNoDuplicateInvoices(): void
    {
        $duplicatePeriod = DB::table('invoices')
            ->selectRaw('customer_id, period, COUNT(*) as total')
            ->groupBy('customer_id', 'period')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicatePeriod) {
            throw new RuntimeException('Tidak bisa pasang constraint: ada invoice dobel untuk customer_id '.$duplicatePeriod->customer_id.' periode '.$duplicatePeriod->period.'.');
        }

        $duplicateNumber = DB::table('invoices')
            ->selectRaw('invoice_number, COUNT(*) as total')
            ->groupBy('invoice_number')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicateNumber) {
            throw new RuntimeException('Tidak bisa pasang constraint: ada nomor invoice dobel '.$duplicateNumber->invoice_number.'.');
        }
    }

    private function assertNoDuplicatePayments(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        $duplicatePayment = DB::table('payments')
            ->selectRaw('invoice_id, COUNT(*) as total')
            ->whereNotNull('invoice_id')
            ->groupBy('invoice_id')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicatePayment) {
            throw new RuntimeException('Tidak bisa pasang constraint: invoice_id '.$duplicatePayment->invoice_id.' punya lebih dari satu pembayaran.');
        }
    }
};
