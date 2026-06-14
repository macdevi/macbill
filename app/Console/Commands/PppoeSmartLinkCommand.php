<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\MikrotikPppoeActiveSession;
use App\Models\MikrotikPppoeProfile;
use App\Models\MikrotikPppoeSecret;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class PppoeSmartLinkCommand extends Command
{
    protected $signature = 'macbilling:pppoe-smart-link {--apply : Terapkan hasil pencocokan aman} {--limit=0 : Batas jumlah data diproses, 0 berarti semua}';

    protected $description = 'Cocokkan PPP Active ke pelanggan lama berdasarkan nama bersih secara aman.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $limit = (int) $this->option('limit');

        $this->line('');
        $this->info('PPPoE Smart Link');
        $this->line('Mode: '.($apply ? 'APPLY' : 'DRY-RUN'));
        $this->line(str_repeat('-', 72));

        $query = MikrotikPppoeActiveSession::query()
            ->with('router')
            ->orderBy('name');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $sessions = $query->get();

        $checked = 0;
        $alreadyLinked = 0;
        $safeMatches = 0;
        $applied = 0;
        $noCandidate = 0;
        $ambiguous = 0;

        foreach ($sessions as $session) {
            $checked++;

            $username = trim((string) $session->name);

            if ($username === '') {
                continue;
            }

            if ($this->existingLinkedCustomer($session->mikrotik_router_id, $username)) {
                $alreadyLinked++;
                continue;
            }

            $cleanName = $this->cleanNameFromPppoeUsername($username);
            $candidates = $this->findExactCandidates($cleanName);

            if ($candidates->count() === 0) {
                $noCandidate++;
                $this->line('[NO] '.$username.' -> '.$cleanName.' | tidak ada pelanggan cocok');
                continue;
            }

            if ($candidates->count() > 1) {
                $ambiguous++;
                $this->warn('[SKIP] '.$username.' -> '.$cleanName.' | kandidat lebih dari 1: '.$candidates->pluck('name')->implode(', '));
                continue;
            }

            $customer = $candidates->first();
            $safeMatches++;

            $this->info('[MATCH] '.$username.' -> '.$customer->name.' [ID '.$customer->id.']');

            if ($apply) {
                $this->linkSessionToCustomer($session, $customer);
                $applied++;
            }
        }

        $this->line(str_repeat('-', 72));
        $this->line('Checked          : '.$checked);
        $this->line('Sudah terhubung  : '.$alreadyLinked);
        $this->line('Match aman       : '.$safeMatches);
        $this->line('Diterapkan       : '.$applied);
        $this->line('Tidak ada kandidat: '.$noCandidate);
        $this->line('Ambigu/dilewati  : '.$ambiguous);

        if (! $apply) {
            $this->warn('Ini masih DRY-RUN. Untuk menerapkan, jalankan: php artisan macbilling:pppoe-smart-link --apply');
        }

        $this->line('');

        return self::SUCCESS;
    }

    private function existingLinkedCustomer($routerId, string $username): ?Customer
    {
        $usernameKey = strtolower(trim($username));

        return Customer::query()
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->whereRaw('LOWER(TRIM(pppoe_username)) = ?', [$usernameKey])
            ->where(function ($query) use ($routerId) {
                $query->where('mikrotik_router_id', $routerId)
                    ->orWhereNull('mikrotik_router_id');
            })
            ->first();
    }

    private function findExactCandidates(string $cleanName)
    {
        $cleanKey = $this->normalizeName($cleanName);

        if ($cleanKey === '') {
            return collect();
        }

        return Customer::query()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('pppoe_username')
                    ->orWhere('pppoe_username', '');
            })
            ->get(['id', 'name', 'phone', 'address'])
            ->filter(function ($customer) use ($cleanKey) {
                return $this->normalizeName((string) $customer->name) === $cleanKey;
            })
            ->values();
    }

    private function linkSessionToCustomer(MikrotikPppoeActiveSession $session, Customer $customer): void
    {
        $secret = MikrotikPppoeSecret::query()
            ->where('mikrotik_router_id', $session->mikrotik_router_id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim((string) $session->name))])
            ->first();

        $profile = null;

        if ($secret?->profile) {
            $profile = MikrotikPppoeProfile::query()
                ->where('mikrotik_router_id', $session->mikrotik_router_id)
                ->where('name', $secret->profile)
                ->first();
        }

        $data = [
            'mikrotik_router_id' => $session->mikrotik_router_id,
            'mikrotik_pppoe_profile_id' => $profile?->id,
            'mikrotik_pppoe_secret_id' => $secret?->id,
            'pppoe_username' => $session->name,
            'pppoe_password' => $secret ? (string) $secret->password : $customer->pppoe_password,
            'mikrotik_sync_status' => $secret ? 'Tersinkron' : 'Belum Sync',
            'mikrotik_synced_at' => $secret ? now() : $customer->mikrotik_synced_at,
            'mikrotik_sync_message' => $secret
                ? 'Dihubungkan otomatis aman dari PPPoE Smart Link dan PPPoE Secret.'
                : 'Dihubungkan otomatis aman dari PPPoE Smart Link. Secret belum dipasangkan.',

            'pppoe_online_status' => 'Online',
            'pppoe_online_at' => now(),
            'pppoe_last_seen_at' => $session->last_seen_at ?: now(),
            'pppoe_remote_address' => $session->address,
            'pppoe_caller_id' => $session->caller_id,
            'pppoe_uptime' => $session->uptime,
        ];

        $safe = [];

        foreach ($data as $key => $value) {
            if (Schema::hasColumn('customers', $key)) {
                $safe[$key] = $value;
            }
        }

        $customer->forceFill($safe)->save();
    }

    private function cleanNameFromPppoeUsername(string $username): string
    {
        $name = trim($username);

        $name = preg_replace('/^\d+/', '', $name);
        $name = preg_replace('/^(User[\-_]?Client[\-_]?|Client[\-_]?|USER[\-_]?CLIENT[\-_]?)/i', '', $name);
        $name = str_replace(['_', '-', '.'], ' ', $name);
        $name = trim(preg_replace('/\s+/', ' ', $name));

        $tokens = collect(explode(' ', $name))
            ->map(fn ($token) => trim($token))
            ->filter()
            ->values();

        if ($tokens->count() > 1) {
            $last = strtolower((string) $tokens->last());

            if (in_array($last, ['r', 'rmh', 'rumah', 'client'], true)) {
                $tokens->pop();
            }
        }

        $name = $tokens->implode(' ');

        if ($name === '') {
            return trim($username);
        }

        return ucwords(strtolower($name));
    }

    private function normalizeName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9]/i', '', $name);

        return $name ?: '';
    }
}
