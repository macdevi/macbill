<?php

namespace App\Services;

use App\Models\AppSetting;

class SettingService
{
    public static function defaults(): array
    {
        return [
            'app_name' => 'MAC-SERVICE',
            'business_name' => 'Macnet RT/RW.NET',
            'owner_name' => '',
            'business_phone' => '',
            'business_whatsapp' => '',
            'business_email' => 'admin@macservice.local',
            'business_address' => '',
            'business_logo' => 'settings/macnet-logo.png',
            'business_favicon' => '',
            'landing_title' => 'Sistem Billing RT/RW.NET Terintegrasi',
            'landing_subtitle' => 'Layanan pelanggan, tagihan, pembayaran, pasang baru',
            'invoice_prefix' => 'INV',
            'receipt_prefix' => 'PAY',
            'invoice_note' => 'Pembayaran melewati jatuh tempo dapat menyebabkan layanan diisolir.',
            'receipt_footer' => 'Terima kasih atas pembayaran Anda.',
            'currency' => 'IDR',
            'timezone' => 'Asia/Jakarta',
            'default_payment_method' => 'Tunai',
            'overdue_months' => '2',
            'map_default_layer' => 'satellite',
        ];
    }

    public static function allMerged(): array
    {
        $defaults = self::defaults();

        $db = AppSetting::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->toArray();

        return array_merge($defaults, $db);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $defaults = self::defaults();

        $setting = AppSetting::query()->where('key', $key)->first();

        if ($setting) {
            return $setting->value;
        }

        return $defaults[$key] ?? $default;
    }

    public static function setMany(array $data, string $group = 'general'): void
    {
        foreach ($data as $key => $value) {
            AppSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_null($value) ? null : (string) $value,
                    'group' => $group,
                    'type' => self::guessType($key),
                ]
            );
        }
    }


    public static function invoicePrefix(): string
    {
        $prefix = trim((string) self::get('invoice_prefix', 'INV'));

        return $prefix !== '' ? strtoupper($prefix) : 'INV';
    }

    public static function receiptPrefix(): string
    {
        $prefix = trim((string) self::get('receipt_prefix', 'PAY'));

        return $prefix !== '' ? strtoupper($prefix) : 'PAY';
    }

    public static function normalizeInvoiceNumber(?string $number, ?string $period = null, ?int $id = null): string
    {
        $prefix = self::invoicePrefix();
        $number = trim((string) $number);

        if ($number === '') {
            $cleanPeriod = self::cleanPeriod($period);
            $seq = str_pad((string) ($id ?: 1), 6, '0', STR_PAD_LEFT);

            return "{$prefix}-{$cleanPeriod}-{$seq}";
        }

        $parts = explode('-', $number);

        if (count($parts) >= 2) {
            $parts[0] = $prefix;

            return implode('-', $parts);
        }

        $cleanPeriod = self::cleanPeriod($period);

        return "{$prefix}-{$cleanPeriod}-{$number}";
    }

    public static function receiptNumber(int|string $id): string
    {
        return self::receiptPrefix().'-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    private static function cleanPeriod(?string $period): string
    {
        $period = trim((string) $period);

        if ($period === '') {
            return now()->format('Ym');
        }

        return str_replace('-', '', $period);
    }


    private static function guessType(string $key): string
    {
        return match ($key) {
            'business_address', 'invoice_note', 'receipt_footer', 'landing_subtitle' => 'textarea',
            'overdue_months' => 'number',
            default => 'text',
        };
    }
}
