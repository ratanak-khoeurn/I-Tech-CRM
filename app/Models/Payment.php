<?php

namespace App\Models;

use App\Support\CampusContext;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank transfer',
        'card' => 'Card',
        'mobile_banking' => 'Mobile banking',
        'other' => 'Other',
    ];

    protected $fillable = [
        'invoice_id', 'payment_date', 'amount', 'exchange_rate', 'method', 'reference', 'received_by', 'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // A payment has no campus of its own: it belongs to whichever campus the student
        // behind its invoice works in, so finance totals never mix campuses.
        static::addGlobalScope('campus', function (Builder $query) {
            CampusContext::whereVisibleInvoice($query);
        });

        // Money collected today is counted at today's rate, and a rate changed later must not
        // restate what was actually handed over on that date.
        static::creating(function (self $payment) {
            $payment->exchange_rate ??= Money::rate();
        });
    }

    /** The riel rate this payment was taken at, falling back to today's rate. */
    public function rate(): float
    {
        $rate = (float) $this->exchange_rate;

        return $rate > 0 ? $rate : Money::rate();
    }

    public function rielLabel(): string
    {
        return Money::rielLabel((float) $this->amount, $this->rate());
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
