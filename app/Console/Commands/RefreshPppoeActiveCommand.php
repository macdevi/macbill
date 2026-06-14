<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\MikrotikPppoeActiveSession;
use App\Models\MikrotikRouter;
use App\Services\MikrotikApiClient;
use Illuminate\Console\Command;
use Throwable;

class RefreshPppoeActiveCommand extends Command
{
    protected $signature = 'macbilling:refresh-pppoe-active {--router_id=}';

    protected $description = 'Refresh PPPoE active sessions from Mikrotik and update customer online/offline status.';

    public function handle(): int
    {
        $routerId = $this->option('router_id');

        $routers = MikrotikRouter::query()
            ->where('status', 'active')
            ->when($routerId, function ($query) use ($routerId) {
                $query->where('id', $routerId);
            })
            ->orderBy('name')
            ->get();

        if ($routers->isEmpty()) {
            $this->warn('Tidak ada router Mikrotik aktif.');
            return self::SUCCESS;
        }

        foreach ($routers as $router) {
            $this->line('Refresh router: '.$router->name.' ['.$router->host.']');

            try {
                $sessions = (new MikrotikApiClient($router))->pppoeActiveSessions();

                MikrotikPppoeActiveSession::query()
                    ->where('mikrotik_router_id', $router->id)
                    ->delete();

                $cleanSessions = [];

                foreach ($sessions as $session) {
                    $name = trim((string) ($session['name'] ?? ''));

                    if ($name === '') {
                        continue;
                    }

                    $cleanSessions[] = [
                        'raw' => $session,
                        'name' => $name,
                        'key' => strtolower($name),
                    ];

                    MikrotikPppoeActiveSession::query()->create([
                        'mikrotik_router_id' => $router->id,
                        'mikrotik_id' => $session['.id'] ?? null,
                        'name' => $name,
                        'service' => $session['service'] ?? null,
                        'caller_id' => $session['caller-id'] ?? null,
                        'address' => $session['address'] ?? null,
                        'uptime' => $session['uptime'] ?? null,
                        'encoding' => $session['encoding'] ?? null,
                        'raw_json' => json_encode($session, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'last_seen_at' => now(),
                    ]);
                }

                Customer::query()
                    ->where('mikrotik_router_id', $router->id)
                    ->whereNotNull('pppoe_username')
                    ->where('pppoe_username', '!=', '')
                    ->update([
                        'pppoe_online_status' => 'Offline',
                    ]);

                $onlineCount = 0;
                $matchedNames = [];
                $unmatchedNames = [];

                foreach ($cleanSessions as $item) {
                    $session = $item['raw'];
                    $usernameKey = $item['key'];

                    $matchedIds = Customer::query()
                        ->whereNotNull('pppoe_username')
                        ->where('pppoe_username', '!=', '')
                        ->where(function ($query) use ($router) {
                            $query->where('mikrotik_router_id', $router->id)
                                ->orWhereNull('mikrotik_router_id');
                        })
                        ->whereRaw('LOWER(TRIM(pppoe_username)) = ?', [$usernameKey])
                        ->pluck('id');

                    if ($matchedIds->isEmpty()) {
                        $unmatchedNames[] = $item['name'];
                        continue;
                    }

                    Customer::query()
                        ->whereIn('id', $matchedIds->all())
                        ->update([
                            'mikrotik_router_id' => $router->id,
                            'pppoe_online_status' => 'Online',
                            'pppoe_online_at' => now(),
                            'pppoe_last_seen_at' => now(),
                            'pppoe_remote_address' => $session['address'] ?? null,
                            'pppoe_caller_id' => $session['caller-id'] ?? null,
                            'pppoe_uptime' => $session['uptime'] ?? null,
                        ]);

                    $onlineCount += $matchedIds->count();
                    $matchedNames[] = $item['name'];
                }

                $configuredCustomers = Customer::query()
                    ->whereNotNull('pppoe_username')
                    ->where('pppoe_username', '!=', '')
                    ->where(function ($query) use ($router) {
                        $query->where('mikrotik_router_id', $router->id)
                            ->orWhereNull('mikrotik_router_id');
                    })
                    ->count();

                $withRouter = Customer::query()
                    ->where('mikrotik_router_id', $router->id)
                    ->whereNotNull('pppoe_username')
                    ->where('pppoe_username', '!=', '')
                    ->count();

                $message = 'Auto refresh PPPoE Active: '.count($cleanSessions).' sesi. Pelanggan cocok: '.$onlineCount.'.';

                if (!empty($unmatchedNames)) {
                    $message .= ' Belum cocok: '.implode(', ', array_slice($unmatchedNames, 0, 10));
                }

                $router->update([
                    'last_test_status' => 'success',
                    'last_test_message' => $message,
                    'last_test_at' => now(),
                ]);

                $this->info('OK: '.count($cleanSessions).' active session, '.$onlineCount.' pelanggan cocok.');
                $this->line('Pelanggan dengan PPPoE username untuk router ini / belum pilih router: '.$configuredCustomers);
                $this->line('Pelanggan PPPoE yang sudah terkait router ini: '.$withRouter);

                if (!empty($matchedNames)) {
                    $this->line('Contoh cocok: '.implode(', ', array_slice($matchedNames, 0, 10)));
                }

                if (!empty($unmatchedNames)) {
                    $this->warn('Contoh belum cocok di data pelanggan: '.implode(', ', array_slice($unmatchedNames, 0, 10)));
                }
            } catch (Throwable $e) {
                $router->update([
                    'last_test_status' => 'failed',
                    'last_test_message' => $e->getMessage(),
                    'last_test_at' => now(),
                ]);

                $this->error('GAGAL: '.$router->name.' - '.$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
