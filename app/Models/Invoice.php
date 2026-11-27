<?php

namespace App\Models;

use App\Models\Concerns\StudentCampusScoped;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use StudentCampusScoped;
    public const STATUS_LABELS = [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'partial' => 'Partially paid',
        'paid' => 'Paid',
        'overdue' => 'Overdue',
        'cancelled' => 'Voided',
    ];

    protected $fillable = [
        'invoice_number', 'student_id', 'student_course_id', 'fee_structure_id', 'issue_date', 'due_date',
        'status', 'discount', 'exchange_rate', 'notes', 'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'discount' => 'decimal:2',
        'exchange_rate' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // A later rate change in Settings must not rewrite money that was already billed, so the
        // invoice keeps the rate of the day it was raised.
        static::creating(function (self $invoice) {
            $invoice->exchange_rate ??= Money::rate();
        });
    }

    /** The riel rate this invoice's figures were quoted at, falling back to today's rate. */
    public function rate(): float
    {
        $rate = (float) $this->exchange_rate;

        return $rate > 0 ? $rate : Money::rate();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function studentCourse(): BelongsTo
    {
        return $this->belongsTo(StudentCourse::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function total(): float
    {
        return max(0, (float) $this->items->sum('amount') - (float) $this->discount);
    }

    public function paidAmount(): float
    {
        return (float) $this->payments->sum('amount');
    }

    public function balance(): float
    {
        return round($this->total() - $this->paidAmount(), 2);
    }

    /** Derive paid/partial from recorded payments; never resurrects cancelled invoices. */
    public function syncStatus(): void
    {
        if (in_array($this->status, ['cancelled', 'draft'], true)) {
            return;
        }
        // Query sums, not loaded relations: callers mutate items/payments right before syncing.
        $total = max(0, (float) $this->items()->sum('amount') - (float) $this->discount);
        $paid = (float) $this->payments()->sum('amount');
        if ($total > 0 && $paid >= $total) {
            $this->status = 'paid';
        } elseif ($paid > 0) {
            $this->status = 'partial';
        } elseif ($this->due_date && $this->due_date->isPast()) {
            $this->status = 'overdue';
        } else {
            $this->status = 'sent';
        }
        $this->save();
    }

    /**
     * What this invoice expects through each collection channel.
     *
     * Lines are already net of their own discount, so the per-method figures add up to the
     * invoice total. Key order follows the line items, which is the order a payment is spent in.
     *
     * @return array<string, array{label: string, due: float, paid: float, balance: float}>
     */
    public function methodBreakdown(): array
    {
        $rows = [];

        foreach ($this->items as $item) {
            $method = $item->payment_method ?: 'cash';
            $rows[$method]['due'] = ($rows[$method]['due'] ?? 0) + $item->net();
        }

        foreach ($this->payments as $payment) {
            $method = $payment->method ?: 'cash';
            $rows[$method]['paid'] = ($rows[$method]['paid'] ?? 0) + (float) $payment->amount;
        }

        foreach ($rows as $method => $row) {
            $rows[$method] = [
                'label' => Payment::METHODS[$method] ?? ucfirst($method),
                'due' => round($row['due'] ?? 0, 2),
                'paid' => round($row['paid'] ?? 0, 2),
                'balance' => round(($row['due'] ?? 0) - ($row['paid'] ?? 0), 2),
            ];
        }

        return $rows;
    }

    /**
     * Spend a cash amount over the invoice's collection channels, in line order.
     *
     * The cashier no longer picks a method per payment: it follows the fee lines the money
     * settles, so one payment can split into several records with the same reference.
     *
     * @return list<array{method: string, amount: float}>
     */
    public function splitByMethods(float $amount): array
    {
        $remaining = round(max(0, $amount), 2);
        $chunks = [];

        foreach ($this->methodBreakdown() as $method => $row) {
            if ($remaining <= 0.009) {
                break;
            }
            $owed = max(0, $row['balance']);
            if ($owed <= 0.009) {
                continue;
            }
            $take = round(min($remaining, $owed), 2);
            $chunks[] = ['method' => $method, 'amount' => $take];
            $remaining = round($remaining - $take, 2);
        }

        if ($remaining > 0.009 && $chunks) {
            // Rounding tail: the last channel carries it so the chunks still total the payment.
            $last = array_key_last($chunks);
            $chunks[$last]['amount'] = round($chunks[$last]['amount'] + $remaining, 2);
        }

        return $chunks;
    }

    /** Sequential INV-YYYY-NNNN number for the current year. */
    public static function nextNumber(): string
    {
        $prefix = 'INV-' . now()->format('Y') . '-';
        // invoice_number is unique centre-wide, so the sequence ignores the campus filter.
        $last = static::withoutGlobalScopes()->where('invoice_number', 'like', $prefix . '%')->max('invoice_number');
        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
