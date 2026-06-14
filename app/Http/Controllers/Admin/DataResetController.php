<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DataResetController extends Controller
{
    public function index()
    {
        $this->ensureAdmin();

        $counts = [
            'customers' => Schema::hasTable('customers') ? DB::table('customers')->count() : 0,
            'invoices' => Schema::hasTable('invoices') ? DB::table('invoices')->count() : 0,
            'payments' => Schema::hasTable('payments') ? DB::table('payments')->count() : 0,
        ];

        return view('admin.settings.reset-data', compact('counts'));
    }

    public function reset(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'confirmation' => ['required', 'string'],
        ]);

        if (trim($request->confirmation) !== 'RESET PELANGGAN') {
            return back()
                ->withInput()
                ->with('error', 'Konfirmasi salah. Ketik RESET PELANGGAN untuk melanjutkan.');
        }

        $databasePath = database_path('database.sqlite');

        if (! File::exists($databasePath)) {
            return back()->with('error', 'Database SQLite tidak ditemukan: ' . $databasePath);
        }

        $backupDir = storage_path('app/backups/reset-data-pelanggan');

        if (! File::isDirectory($backupDir)) {
            File::ensureDirectoryExists($backupDir, 0775, true);
        }

        $backupFile = $backupDir . '/database-before-reset-pelanggan-' . now()->format('Ymd-His') . '.sqlite';
        File::copy($databasePath, $backupFile);

        $before = [
            'customers' => Schema::hasTable('customers') ? DB::table('customers')->count() : 0,
            'invoices' => Schema::hasTable('invoices') ? DB::table('invoices')->count() : 0,
            'payments' => Schema::hasTable('payments') ? DB::table('payments')->count() : 0,
        ];

        DB::beginTransaction();

        try {
            DB::statement('PRAGMA foreign_keys=OFF');

            if (Schema::hasTable('payments')) {
                DB::table('payments')->delete();
            }

            if (Schema::hasTable('invoices')) {
                DB::table('invoices')->delete();
            }

            if (Schema::hasTable('customers')) {
                DB::table('customers')->delete();
            }

            if (Schema::hasTable('sqlite_sequence')) {
                DB::table('sqlite_sequence')
                    ->whereIn('name', ['payments', 'invoices', 'customers'])
                    ->delete();
            }

            DB::statement('PRAGMA foreign_keys=ON');

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Reset gagal: ' . $e->getMessage());
        }

        return redirect('/admin/settings/reset-data')
            ->with('success', 'Reset data pelanggan berhasil. Customer, invoice, dan riwayat pembayaran sudah dibersihkan.')
            ->with('backup_file', $backupFile)
            ->with('before_counts', $before);
    }

    private function ensureAdmin(): void
    {
        if (! Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengakses halaman ini.');
        }
    }
}
