<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public static function openStatuses(): array
    {
        return ['Belum Bayar', 'Nunggak'];
    }

    public static function paidStatuses(): array
    {
        return ['Bayar Awal', 'Lunas'];
    }

    public static function cleanPeriod(?string $period): string
    {
        $period = $period ?: now()->format('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            return now()->format('Y-m');
        }

        return $period;
    }

    public static function invoiceNumber(string $period, int $customerId): string
    {
        return SettingService::normalizeInvoiceNumber(null, $period, $customerId);
    }

    public static function dueDate(string $period, int $billingDay): string
    {
        $period = self::cleanPeriod($period);

        $base = Carbon::createFromFormat('Y-m-d', $period . '-01');
        $lastDay = $base->copy()->endOfMonth()->day;
        $day = max(1, min($billingDay, $lastDay));

        return $base->copy()->day($day)->toDateString();
    }

    public static function paidStatusFor($dueDate, $paidAt = null): string
    {
        /*
         * Aturan bisnis:
         * - Jika invoice sudah dibayar dan hari ini masih sebelum due_date, status = Bayar Awal.
         * - Saat hari ini sudah sama dengan / melewati due_date, status = Lunas.
         *
         * paidAt tetap diterima agar kompatibel dengan pemanggilan lama,
         * tetapi penentuan Bayar Awal -> Lunas mengikuti tanggal hari ini.
         */
        $today = now()->startOfDay();
        $due = Carbon::parse($dueDate)->startOfDay();

        if ($today->lt($due)) {
            return 'Bayar Awal';
        }

        return 'Lunas';
    }

    public static function unpaidStatusFor($dueDate, int $openInvoiceCount = 1): string
    {
        /*
         * Aturan bisnis MAC Billing:
         * - Belum Bayar: belum dibayar dan belum lewat lebih dari 1 bulan dari due_date.
         * - Nunggak: belum dibayar dan sudah lewat lebih dari 1 bulan dari due_date.
         *
         * Tidak memakai toleransi harian.
         * Jumlah invoice terbuka tidak otomatis membuat status Nunggak.
         */
        $today = now()->startOfDay();
        $due = Carbon::parse($dueDate)->startOfDay();
        $nunggakAfter = $due->copy()->addMonthNoOverflow();

        if ($today->gt($nunggakAfter)) {
            return 'Nunggak';
        }

        return 'Belum Bayar';
    }


    public static function syncStatuses(): int
    {
        $updated = 0;

        $openCounts = Invoice::query()
            ->whereNull('paid_at')
            ->whereIn('status', self::openStatuses())
            ->selectRaw('customer_id, COUNT(*) as total')
            ->groupBy('customer_id')
            ->pluck('total', 'customer_id')
            ->toArray();

        $invoices = Invoice::query()->get();

        foreach ($invoices as $invoice) {
            if ($invoice->paid_at) {
                $newStatus = self::paidStatusFor($invoice->due_date, $invoice->paid_at);
            } else {
                $count = (int) ($openCounts[$invoice->customer_id] ?? 1);
                $newStatus = self::unpaidStatusFor($invoice->due_date, $count);
            }

            if ($invoice->status !== $newStatus) {
                $invoice->update(['status' => $newStatus]);
                $updated++;
            }
        }

        return $updated;
    }

    public static function preview(string $period)
    {
        $period = self::cleanPeriod($period);

        self::syncStatuses();

        return Customer::with('package')
            ->orderBy('id')
            ->get()
            ->map(function (Customer $customer) use ($period) {
                $existing = Invoice::where('customer_id', $customer->id)
                    ->where('period', $period)
                    ->first();

                $amount = (int) $customer->monthly_price;
                $active = $customer->status === 'active';
                $dueDate = self::dueDate($period, (int) $customer->billing_day);

                $canGenerate = true;
                $statusText = 'Siap Dibuat';
                $statusClass = 'green';

                if (!$active) {
                    $canGenerate = false;
                    $statusText = 'Nonaktif';
                    $statusClass = 'red';
                } elseif ($customer->monthly_price === null || $customer->monthly_price === '' || $amount < 0) {
                    $canGenerate = false;
                    $statusText = 'Nominal Kosong';
                    $statusClass = 'yellow';
                } elseif ($amount === 0) {
                    $canGenerate = false;
                    $statusText = 'Gratis';
                    $statusClass = 'blue';
                } elseif ($existing) {
                    $canGenerate = false;
                    $statusText = 'Sudah Ada - ' . $existing->status;

                    $statusClass = match ($existing->status) {
                        'Bayar Awal' => 'blue',
                        'Lunas' => 'green',
                        'Nunggak' => 'red',
                        default => 'yellow',
                    };
                }

                return (object) [
                    'customer' => $customer,
                    'package' => $customer->package,
                    'period' => $period,
                    'invoice_number' => self::invoiceNumber($period, $customer->id),
                    'due_date' => $dueDate,
                    'amount' => $amount,
                    'existing' => $existing,
                    'can_generate' => $canGenerate,
                    'status_text' => $statusText,
                    'status_class' => $statusClass,
                ];
            });
    }

    public static function generateSelected(string $period, array $customerIds): array
    {
        $period = self::cleanPeriod($period);
        $ids = array_values(array_unique(array_filter(array_map('intval', $customerIds))));

        $result = [
            'created' => 0,
            'existing' => 0,
            'inactive' => 0,
            'no_price' => 0,
            'free_customer' => 0,
            'errors' => [],
        ];

        DB::transaction(function () use ($period, $ids, &$result) {
            foreach ($ids as $customerId) {
                try {
                    $customer = Customer::with('package')->find($customerId);

                    if (!$customer) {
                        $result['errors'][] = "Pelanggan #{$customerId} tidak ditemukan.";
                        continue;
                    }

                    if ($customer->status !== 'active') {
                        $result['inactive']++;
                        continue;
                    }

                    $amount = (int) $customer->monthly_price;

                    if ($customer->monthly_price === null || $customer->monthly_price === '' || $amount < 0) {
                        $result['no_price']++;
                        continue;
                    }

                    if ($amount === 0) {
                        $result['free_customer']++;
                        continue;
                    }

                    $existing = Invoice::where('customer_id', $customer->id)
                        ->where('period', $period)
                        ->first();

                    if ($existing) {
                        $result['existing']++;
                        continue;
                    }

                    Invoice::create([
                        'invoice_number' => self::invoiceNumber($period, $customer->id),
                        'customer_id' => $customer->id,
                        'package_id' => $customer->package_id,
                        'period' => $period,
                        'due_date' => self::dueDate($period, (int) $customer->billing_day),
                        'amount' => $amount,
                        'paid_amount' => 0,
                        'status' => 'Belum Bayar',
                        'paid_at' => null,
                        'payment_method' => null,
                        'notes' => null,
                    ]);

                    $result['created']++;
                } catch (\Throwable $e) {
                    if (str_contains(strtolower($e->getMessage()), 'unique')) {
                        $result['existing']++;
                    } else {
                        $result['errors'][] = "Pelanggan #{$customerId}: " . $e->getMessage();
                    }
                }
            }
        });

        self::syncStatuses();

        return $result;
    }

    public static function autoGenerateDueInvoices($date = null): array
    {
        /*
         * Auto billing produksi:
         * - Membuat invoice otomatis untuk pelanggan aktif.
         * - Invoice dibuat jika due_date periode berjalan <= tanggal proses.
         * - Jika cron/VPS sempat mati pada tanggal billing, sistem tetap mengejar tagihan yang terlewat.
         * - Tetap aman dari invoice dobel karena 1 customer + 1 period selalu dicek sebelum create.
         */
        $runDate = $date ? Carbon::parse($date)->startOfDay() : now()->startOfDay();
        $period = $runDate->format('Y-m');

        $result = [
            'date' => $runDate->toDateString(),
            'period' => $period,
            'checked' => 0,
            'created' => 0,
            'created_today' => 0,
            'created_missed' => 0,
            'existing' => 0,
            'inactive' => 0,
            'no_price' => 0,
            'free_customer' => 0,
            'not_due_today' => 0,
            'errors' => [],
        ];

        DB::transaction(function () use ($runDate, $period, &$result) {
            $customers = Customer::with('package')->orderBy('id')->get();

            foreach ($customers as $customer) {
                $result['checked']++;

                try {
                    if ($customer->status !== 'active') {
                        $result['inactive']++;
                        continue;
                    }

                    $amount = (int) $customer->monthly_price;

                    if ($customer->monthly_price === null || $customer->monthly_price === '' || $amount < 0) {
                        $result['no_price']++;
                        continue;
                    }

                    if ($amount === 0) {
                        $result['free_customer']++;
                        continue;
                    }

                    $dueDateString = self::dueDate($period, (int) $customer->billing_day);
                    $dueDate = Carbon::parse($dueDateString)->startOfDay();

                    if ($dueDate->gt($runDate)) {
                        $result['not_due_today']++;
                        continue;
                    }

                    $existing = Invoice::where('customer_id', $customer->id)
                        ->where('period', $period)
                        ->first();

                    if ($existing) {
                        $result['existing']++;
                        continue;
                    }

                    $isMissed = $dueDate->lt($runDate);

                    Invoice::create([
                        'invoice_number' => self::invoiceNumber($period, $customer->id),
                        'customer_id' => $customer->id,
                        'package_id' => $customer->package_id,
                        'period' => $period,
                        'due_date' => $dueDateString,
                        'amount' => $amount,
                        'paid_amount' => 0,
                        'status' => 'Belum Bayar',
                        'paid_at' => null,
                        'payment_method' => null,
                        'notes' => $isMissed
                            ? 'Auto generated by cron after missed due date'
                            : 'Auto generated by cron on due date',
                    ]);

                    $result['created']++;

                    if ($isMissed) {
                        $result['created_missed']++;
                    } else {
                        $result['created_today']++;
                    }
                } catch (\Throwable $e) {
                    if (str_contains(strtolower($e->getMessage()), 'unique')) {
                        $result['existing']++;
                    } else {
                        $result['errors'][] = "Customer #{$customer->id}: " . $e->getMessage();
                    }
                }
            }
        });

        $result['synced'] = self::syncStatuses();

        return $result;
    }


    public static function payInvoices(array $invoiceIds, string $method = 'cash', ?int $collectorId = null, ?string $notes = null): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $invoiceIds))));

        $result = [
            'paid' => 0,
            'skipped' => 0,
            'amount' => 0,
        ];

        DB::transaction(function () use ($ids, $method, $collectorId, $notes, &$result) {
            foreach ($ids as $invoiceId) {
                $invoice = Invoice::query()
                    ->whereKey($invoiceId)
                    ->lockForUpdate()
                    ->first();

                if (! $invoice) {
                    $result['skipped']++;
                    continue;
                }

                if ($invoice->paid_at || in_array($invoice->status, self::paidStatuses(), true)) {
                    $result['skipped']++;
                    continue;
                }

                if (! in_array($invoice->status, self::openStatuses(), true)) {
                    $result['skipped']++;
                    continue;
                }

                $invoiceAmount = max(0, (int) $invoice->amount);
                $alreadyPaid = max(0, (int) $invoice->paid_amount);
                $remainingAmount = max(0, $invoiceAmount - $alreadyPaid);

                if ($invoiceAmount <= 0 || $remainingAmount <= 0) {
                    $result['skipped']++;
                    continue;
                }

                $paidAt = now();
                $status = self::paidStatusFor($invoice->due_date, $paidAt);

                $invoice->update([
                    'paid_amount' => $invoiceAmount,
                    'paid_at' => $paidAt,
                    'payment_method' => $method,
                    'status' => $status,
                    'notes' => $notes,
                ]);

                Payment::create([
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'collector_id' => $collectorId,
                    'amount' => $remainingAmount,
                    'method' => $method,
                    'notes' => $notes,
                    'paid_at' => $paidAt,
                ]);

                $result['paid']++;
                $result['amount'] += $remainingAmount;
            }
        });

        self::syncStatuses();

        return $result;
    }
}
