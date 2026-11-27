<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    public const DISCOUNT_UNITS = ['amount' => '$', 'percent' => '%'];

    protected $fillable = [
        'invoice_id', 'description', 'quantity', 'unit_price', 'payment_method', 'discount', 'discount_type', 'amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
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

    /** What this line actually charges after its own discount. */
    public function net(): float
    {
        return round(max(0, (float) $this->amount - (float) $this->discount), 2);
    }

    /** Discount in dollars, annotated with the rate when the cashier entered a percentage. */
    public function discountLabel(): string
    {
        $dollars = number_format((float) $this->discount, 2);
        $amount = (float) $this->amount;

        if ($this->discount_type !== 'percent' || $amount <= 0) {
            return '$' . $dollars;
        }

        $rate = number_format((float) $this->discount / $amount * 100, 2, '.', '');

        return '$' . $dollars . ' (' . rtrim(rtrim($rate, '0'), '.') . '%)';
    }

    /** The stored dollars re-expressed in this line's own unit, for prefilling the form. */
    public function discountInOwnUnit(): float
    {
        $amount = (float) $this->amount;

        if ($this->discount_type !== 'percent' || $amount <= 0) {
            return round((float) $this->discount, 2);
        }

        return round((float) $this->discount / $amount * 100, 2);
    }
}
