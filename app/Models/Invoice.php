<?php

namespace App\Models;

use App\Models\Concerns\StudentCampusScoped;
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
        'status', 'discount', 'notes', 'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'discount' => 'decimal:2',
    ];

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
