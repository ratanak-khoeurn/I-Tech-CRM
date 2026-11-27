<?php

namespace App\Support;

use App\Models\Setting;

class Money
{
    /** Ledger values are always US dollars; Riel only ever describes how an amount was typed in. */
    public const CURRENCIES = ['USD' => '$', 'KHR' => '៛'];

    public const DEFAULT_RATE = 4100;

    public static function rate(): float
    {
        $rate = (float) Setting::get('riel_rate', (string) self::DEFAULT_RATE);

        return $rate > 0 ? $rate : (float) self::DEFAULT_RATE;
    }

    public static function isRiel(?string $currency): bool
    {
        return ($currency ?? 'USD') === 'KHR';
    }

    public static function sign(?string $currency): string
    {
        return self::CURRENCIES[$currency] ?? self::CURRENCIES['USD'];
    }

    public static function toUsd(float $amount, ?string $currency, ?float $rate = null): float
    {
        $rate = $rate ?? self::rate();

        return self::isRiel($currency) ? round($amount / $rate, 2) : round($amount, 2);
    }

    public static function toRiel(float $usd, ?float $rate = null): float
    {
        return round($usd * ($rate ?? self::rate()), 2);
    }

    /** Notes and receipts quote Riel in whole riels, the way it changes hands. */
    public static function rielLabel(float $usd, ?float $rate = null): string
    {
        return self::CURRENCIES['KHR'] . number_format(round(self::toRiel($usd, $rate)));
    }
}
