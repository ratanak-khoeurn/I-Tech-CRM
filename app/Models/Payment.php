<?php

namespace App\Models;

use App\Support\CampusContext;
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
        'invoice_id', 'payment_date', 'amount', 'method', 'reference', 'received_by', 'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // A payment has no campus of its own: it belongs to whichever campus the student
        // behind its invoice works in, so finance totals never mix campuses.
        static::addGlobalScope('campus', function (Builder $query) {
            CampusContext::whereVisibleInvoice($query);
        });
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
