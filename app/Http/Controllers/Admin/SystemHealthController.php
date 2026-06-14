<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MikrotikPppoeActiveSession;
use App\Models\MikrotikRouter;
use App\Models\Payment;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class SystemHealthController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }

    public function index()
    {
        $this->ensureAdmin();

        $dbStatus = $this->checkDatabase();

        $cronBilling = $this->checkCronFile('/etc/cron.d/macbilling-daily-billing', 'macbilling:daily-billing');
        $cronPppoe = $this->checkCronFile('/etc/cron.d/macbilling-refresh-pppoe-active', 'macbilling:refresh-pppoe-active');

        $pppoeLog = $this->readTail(storage_path('logs/pppoe-active-refresh.log'), 30);
        $billingLog = $this->readTail(storage_path('logs/daily-billing.log'), 30);
        $laravelLog = $this->readTail(storage_path('logs/laravel.log'), 40, true);

        $databaseFile = database_path('database.sqlite');

        $summary = [
            'app_name' => SettingService::get('app_name', 'MAC-SERVICE'),
            'timezone' => config('app.timezone'),
            'now' => now()->format('d/m/Y H:i:s'),
            'env' => config('app.env'),
            'php' => PHP_VERSION,
            'laravel' => app()->version(),

            'database_file' => $databaseFile,
            'database_size' => File::exists($databaseFile) ? $this->formatBytes(File::size($databaseFile)) : '-',

            'disk_total' => $this->formatBytes((int) @disk_total_space(base_path())),
            'disk_free' => $this->formatBytes((int) @disk_free_space(base_path())),

            'customers_total' => Customer::query()->count(),
            'customers_active' => Customer::query()->where('status', 'active')->count(),
            'customers_free' => Customer::query()->where('status', 'active')->where('monthly_price', 0)->count(),

            'invoices_total' => Invoice::query()->count(),
            'invoices_open' => Invoice::query()->whereIn('status', ['Belum Bayar', 'Nunggak'])->count(),
            'invoices_early' => Invoice::query()->where('status', 'Bayar Awal')->count(),
            'invoices_paid' => Invoice::query()->where('status', 'Lunas')->count(),

            'payments_total' => Payment::query()->count(),
            'payments_today' => Payment::query()->whereDate('paid_at', now()->toDateString())->count(),
            'payments_today_amount' => Payment::query()->whereDate('paid_at', now()->toDateString())->sum('amount'),

            'routers_total' => MikrotikRouter::query()->count(),
            'routers_active' => MikrotikRouter::query()->where('status', 'active')->count(),

            'active_sessions' => MikrotikPppoeActiveSession::query()->count(),
            'last_pppoe_seen' => MikrotikPppoeActiveSession::query()->max('last_seen_at'),

            'pppoe_customers' => Customer::query()->whereNotNull('pppoe_username')->where('pppoe_username', '!=', '')->count(),
            'online_customers' => Customer::query()->where('pppoe_online_status', 'Online')->count(),
            'offline_customers' => Customer::query()->where('pppoe_online_status', 'Offline')->count(),
        ];

        $routers = MikrotikRouter::query()
            ->orderByDesc('status')
            ->orderBy('name')
            ->get();

        return view('admin.system.health', compact(
            'dbStatus',
            'cronBilling',
            'cronPppoe',
            'pppoeLog',
            'billingLog',
            'laravelLog',
            'summary',
            'routers'
        ));
    }

    public function refreshPppoe(Request $request)
    {
        $this->ensureAdmin();

        return $this->runCommand('macbilling:refresh-pppoe-active', 'Refresh PPPoE Active selesai.');
    }

    public function runBilling(Request $request)
    {
        $this->ensureAdmin();

        return $this->runCommand('macbilling:daily-billing', 'Auto billing manual selesai.');
    }

    public function runAudit(Request $request)
    {
        $this->ensureAdmin();

        return $this->runCommand('macbilling:audit', 'Audit data selesai.');
    }

    private function runCommand(string $command, string $successMessage)
    {
        try {
            if (! array_key_exists($command, Artisan::all())) {
                return back()->with('error', 'Command belum terdaftar: '.$command);
            }

            Artisan::call($command);
            $output = trim(Artisan::output());

            return back()
                ->with('success', $successMessage)
                ->with('command_name', $command)
                ->with('command_output', $output ?: 'Command selesai tanpa output.');
        } catch (Throwable $e) {
            return back()->with('error', 'Command gagal: '.$e->getMessage());
        }
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('select 1 as ok');

            return [
                'status' => 'OK',
                'message' => 'Database terkoneksi.',
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'ERROR',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function checkCronFile(string $path, string $command): array
    {
        $exists = File::exists($path);
        $content = $exists ? File::get($path) : '';

        return [
            'path' => $path,
            'command' => $command,
            'exists' => $exists,
            'enabled' => $exists && str_contains($content, $command),
            'content' => trim($content),
            'modified_at' => $exists ? date('d/m/Y H:i:s', filemtime($path)) : null,
        ];
    }

    private function readTail(string $path, int $lines = 30, bool $errorOnly = false): array
    {
        if (! File::exists($path)) {
            return [
                'path' => $path,
                'exists' => false,
                'lines' => [],
                'modified_at' => null,
            ];
        }

        $content = File::get($path);
        $rows = preg_split('/\r\n|\r|\n/', trim($content));

        if ($errorOnly) {
            $rows = array_values(array_filter($rows, function ($line) {
                return stripos($line, 'error') !== false
                    || stripos($line, 'exception') !== false
                    || stripos($line, 'failed') !== false
                    || stripos($line, 'fatal') !== false
                    || stripos($line, 'production.ERROR') !== false;
            }));
        }

        return [
            'path' => $path,
            'exists' => true,
            'lines' => array_slice($rows, -$lines),
            'modified_at' => date('d/m/Y H:i:s', filemtime($path)),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
