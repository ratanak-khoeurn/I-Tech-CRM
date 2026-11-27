<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    public const DISCOUNT_UNITS = ['amount' => '$', 'percent' => '%'];

    protected $fillable = [
        'invoice_id', 'description', 'currency', 'quantity', 'unit_price', 'entered_unit_price',
        'payment_method', 'discount', 'entered_discount', 'discount_type', 'amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'entered_unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'entered_discount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function methodLabel(): string
    {
        return Payment::METHODS[$this->payment_method] ?? ucfirst((string) $this->payment_method);
    }

    /** What this line actually charges after its own discount, in dollars. */
    public function net(): float
    {
        return round(max(0, (float) $this->amount - (float) $this->discount), 2);
    }

    public function isRiel(): bool
    {
        return $this->currency === 'KHR';
    }

    public function currencySign(): string
    {
        return Money::sign($this->currency);
    }

    public function typedUnitPrice(): float
    {
        return $this->isRiel() ? (float) $this->entered_unit_price : (float) $this->unit_price;
    }

    /** Gross of this line in the currency it was quoted in. */
    public function typedAmount(): float
    {
        return round($this->typedUnitPrice() * $this->quantity, 2);
    }

    /** Discount in the currency this line was quoted in; a percentage applies to either currency. */
    public function typedDiscount(): float
    {
        if ($this->discount_type === 'percent') {
            $ratio = (float) $this->amount > 0 ? (float) $this->discount / (float) $this->amount : 0;

            return round($this->typedAmount() * min($ratio, 1), 2);
        }

        return $this->isRiel() ? (float) $this->entered_discount : (float) $this->discount;
    }

    public function typedNet(): float
    {
        return round(max(0, $this->typedAmount() - $this->typedDiscount()), 2);
    }

    /** Riel changes hands in whole riels, so riel figures print without decimals. */
    public function typedLabel(float $value): string
    {
        return $this->isRiel()
            ? $this->currencySign() . number_format(round($value))
            : $this->currencySign() . number_format($value, 2);
    }

    public function unitPriceLabel(): string
    {
        return $this->typedLabel($this->typedUnitPrice());
    }

    public function netLabel(): string
    {
        return $this->typedLabel($this->typedNet());
    }

    /** Discount in this line's own currency, annotated with the rate when it was entered as a percentage. */
    public function discountLabel(): string
    {
        $off = $this->typedDiscount();
        $base = $this->typedAmount();

        if ($this->discount_type !== 'percent' || $base <= 0) {
            return $this->typedLabel($off);
        }

        $percent = number_format($off / $base * 100, 2, '.', '');

        return $this->typedLabel($off) . ' (' . rtrim(rtrim($percent, '0'), '.') . '%)';
    }

    /** The stored figure re-expressed in this line's own unit, for prefilling the form. */
    public function discountInOwnUnit(): float
    {
        if ($this->discount_type !== 'percent') {
            return $this->isRiel() ? (float) $this->entered_discount : round((float) $this->discount, 2);
        }

        $amount = (float) $this->amount;

        return $amount > 0 ? round((float) $this->discount / $amount * 100, 2) : 0.0;
    }
}
