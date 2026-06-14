<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetCustomerDataController extends Controller
{
    public function handle(Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->confirm();
        }

        return $this->destroy($request);
    }

    public function confirm()
    {
        $customerCount = $this->safeCount('customers');
        $invoiceCount = $this->safeCount('invoices');
        $paymentCount = $this->safeCount('payments');

        return response()->view('admin.settings.reset-customers-safe', compact(
            'customerCount',
            'invoiceCount',
            'paymentCount'
        ));
    }

    public function destroy(Request $request)
    {
        $driver = DB::connection()->getDriverName();

        try {
            $this->disableForeignKeys($driver);

            DB::beginTransaction();

            $deleted = [];

            $tables = $this->tableNames();

            /*
             * Hapus tabel turunan lebih dulu.
             * Semua tabel yang punya invoice_id atau customer_id dianggap data terkait pelanggan.
             */
            foreach ($tables as $table) {
                if ($this->skipTable($table)) {
                    continue;
                }

                if (Schema::hasColumn($table, 'invoice_id')) {
                    $deleted[$table] = ($deleted[$table] ?? 0) + DB::table($table)->delete();
                }
            }

            foreach ($tables as $table) {
                if ($this->skipTable($table)) {
                    continue;
                }

                if (Schema::hasColumn($table, 'customer_id')) {
                    $deleted[$table] = ($deleted[$table] ?? 0) + DB::table($table)->delete();
                }
            }

            /*
             * Tabel umum yang berkaitan dengan pelanggan.
             * Hapus jika ada, abaikan jika tidak ada.
             */
            foreach ([
                'payments',
                'payment_details',
                'invoice_payments',
                'invoices',
                'customer_orders',
                'orders',
                'registrations',
                'installation_orders',
                'customers',
            ] as $table) {
                if (Schema::hasTable($table) && !$this->skipTable($table)) {
                    $deleted[$table] = ($deleted[$table] ?? 0) + DB::table($table)->delete();
                }
            }

            $this->resetSequences(array_keys($deleted), $driver);

            DB::commit();

            $this->enableForeignKeys($driver);

            return redirect()
                ->back()
                ->with('success', 'Data pelanggan berhasil direset.');
        } catch (\Throwable $e) {
            try {
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
            } catch (\Throwable $ignored) {
                //
            }

            try {
                $this->enableForeignKeys($driver);
            } catch (\Throwable $ignored) {
                //
            }

            report($e);

            return redirect()
                ->back()
                ->with('error', 'Reset data pelanggan gagal: '.$e->getMessage());
        }
    }

    private function safeCount(string $table): int
    {
        try {
            return Schema::hasTable($table) ? (int) DB::table($table)->count() : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function tableNames(): array
    {
        $driver = DB::connection()->getDriverName();

        try {
            if ($driver === 'sqlite') {
                return collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"))
                    ->pluck('name')
                    ->values()
                    ->all();
            }

            if ($driver === 'mysql') {
                $database = DB::getDatabaseName();

                return collect(DB::select(
                    "SELECT table_name AS name FROM information_schema.tables WHERE table_schema = ?",
                    [$database]
                ))->pluck('name')->values()->all();
            }

            return collect(Schema::getTables())
                ->pluck('name')
                ->values()
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function skipTable(string $table): bool
    {
        return in_array($table, [
            'migrations',
            'users',
            'roles',
            'permissions',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'password_reset_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
            'personal_access_tokens',
            'packages',
            'odps',
            'odp_ports',
            'settings',
        ], true);
    }

    private function disableForeignKeys(string $driver): void
    {
        try {
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
                return;
            }

            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                return;
            }

            Schema::disableForeignKeyConstraints();
        } catch (\Throwable $e) {
            //
        }
    }

    private function enableForeignKeys(string $driver): void
    {
        try {
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
                return;
            }

            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                return;
            }

            Schema::enableForeignKeyConstraints();
        } catch (\Throwable $e) {
            //
        }
    }

    private function resetSequences(array $tables, string $driver): void
    {
        try {
            if ($driver === 'sqlite' && Schema::hasTable('sqlite_sequence')) {
                foreach ($tables as $table) {
                    DB::table('sqlite_sequence')->where('name', $table)->delete();
                }
            }

            if ($driver === 'mysql') {
                foreach ($tables as $table) {
                    if (Schema::hasTable($table)) {
                        DB::statement("ALTER TABLE `$table` AUTO_INCREMENT = 1");
                    }
                }
            }
        } catch (\Throwable $e) {
            //
        }
    }
}
