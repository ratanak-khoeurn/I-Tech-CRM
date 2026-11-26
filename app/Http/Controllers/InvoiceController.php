<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query()->with(['student:id,name_english,student_code', 'items', 'payments'])
            ->withCount('items')->latest('issue_date');
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }
        if ($q = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('invoice_number', 'like', "%$q%")
                ->orWhereHas('student', fn ($w2) => $w2->where('name_english', 'like', "%$q%")->orWhere('student_code', 'like', "%$q%")));
        }
        $invoices = $query->paginate(15)->withQueryString();
        $students = Student::where('record_status', 'active')->orderBy('name_english')->get(['id', 'name_english', 'student_code']);

        $collected = (float) Payment::whereHas('invoice', fn ($w) => $this->applyFilters($w, $request))->sum('amount');
        $outstanding = 0.0;
        foreach (Invoice::cursor() as $inv) {
            if (in_array($inv->status, ['cancelled'], true)) {
                continue;
            }
            $outstanding += max(0, $inv->balance());
        }

        return view('finance.invoices', compact('invoices', 'students', 'collected', 'outstanding'));
    }

    public function create(Request $request)
    {
        $students = Student::with(['studentCourses.course'])
            ->where('record_status', 'active')
            ->orderBy('name_english')
            ->get(['id', 'name_english', 'student_code']);
        $fees = FeeStructure::where('is_active', true)->with('course')->orderBy('name')->get();
        $selectedStudent = $students->firstWhere('id', (int) $request->query('student_id'));

        return view('finance.invoice-form', ['invoice' => new Invoice([
            'student_id' => $selectedStudent?->id,
            'student_course_id' => $request->query('student_course_id'),
            'issue_date' => now(),
            'status' => 'draft',
        ]), 'students' => $students, 'fees' => $fees]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $items = $this->validateItems($request);
        $data['discount'] = $this->resolveDiscount($data, $items);
        unset($data['discount_type'], $data['discount_value']);

        $invoice = DB::transaction(function () use ($request, $data, $items) {
            $invoice = new Invoice($data);
            $invoice->invoice_number = $this->nextNumber();
            $invoice->created_by = Auth::id();
            $invoice->save();
            $this->saveItems($invoice, $items);

            return $invoice;
        });

        AuditLog::record('created', 'invoice', $invoice->id, "Created invoice {$invoice->invoice_number}");

        return redirect()->route('invoices.show', $invoice)->with('success', "Invoice {$invoice->invoice_number} created.");
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['student.studentCourses.course', 'items', 'payments.receiver', 'feeStructure']);

        return view('finance.invoice-show', ['invoice' => $invoice]);
    }

    public function edit(Invoice $invoice)
    {
        abort_if(in_array($invoice->status, ['paid', 'cancelled'], true), 403, 'Paid or cancelled invoices cannot be edited. Record a payment or create a new invoice instead.');
        $invoice->load('items');
        $students = Student::with(['studentCourses.course'])
            ->where(fn ($query) => $query->where('record_status', 'active')->orWhere('id', $invoice->student_id))
            ->orderBy('name_english')
            ->get(['id', 'name_english', 'student_code']);
        $fees = FeeStructure::where('is_active', true)->with('course')->orderBy('name')->get();

        return view('finance.invoice-form', compact('invoice', 'students', 'fees'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        abort_if(in_array($invoice->status, ['paid', 'cancelled'], true), 403, 'Paid or cancelled invoices cannot be edited.');
        $data = $request->validate($this->rules());
        $items = $this->validateItems($request);
        $data['discount'] = $this->resolveDiscount($data, $items);
        unset($data['discount_type'], $data['discount_value']);

        DB::transaction(function () use ($invoice, $data, $items) {
            $invoice->update($data);
            $invoice->items()->delete();
            $this->saveItems($invoice, $items);
            $invoice->syncStatus();
        });

        AuditLog::record('updated', 'invoice', $invoice->id, "Updated invoice {$invoice->invoice_number}");

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['draft', 'sent', 'cancelled'])],
        ]);
        if ($invoice->status === 'paid' && $data['status'] !== 'cancelled') {
            return back()->with('error', 'A paid invoice can only be voided; its payment status cannot be reset.');
        }
        $invoice->status = $data['status'];
        $invoice->save();
        $paymentCount = $invoice->payments()->count();
        AuditLog::record(
            $data['status'] === 'cancelled' ? 'voided' : 'status_changed',
            'invoice',
            $invoice->id,
            "Invoice {$invoice->invoice_number} → " . ($data['status'] === 'cancelled' ? "voided; {$paymentCount} payment(s) retained" : $data['status']),
        );

        return $data['status'] === 'cancelled'
            ? back()->with('success', "Invoice {$invoice->invoice_number} was voided. {$paymentCount} recorded payment(s) were retained.")
            : back()->with('success', 'Invoice status updated.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->payments()->exists()) {
            return back()->with('error', 'Cannot delete an invoice that has payments recorded.');
        }
        $invoice->delete();
        AuditLog::record('deleted', 'invoice', $invoice->id, "Deleted invoice {$invoice->invoice_number}");

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted.');
    }

    public function storePayment(Request $request, Invoice $invoice)
    {
        abort_if($invoice->status === 'cancelled', 403, 'This invoice is cancelled.');
        $data = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        if ((float) $data['amount'] > $invoice->balance() + 0.009) {
            return back()->with('error', 'Payment exceeds the outstanding balance of ' . number_format($invoice->balance(), 2) . '.');
        }
        $data['received_by'] = Auth::id();
        $invoice->payments()->create($data);
        $invoice->syncStatus();
        AuditLog::record('created', 'payment', $invoice->id, "Payment of {$data['amount']} on {$invoice->invoice_number}");

        return back()->with('success', 'Payment recorded.');
    }

    public function destroyPayment(Payment $payment)
    {
        $invoice = $payment->invoice;
        $payment->delete();
        $invoice->syncStatus();
        AuditLog::record('deleted', 'payment', $invoice->id, "Deleted payment on {$invoice->invoice_number}");

        return back()->with('success', 'Payment deleted.');
    }

    public function payments(Request $request)
    {
        $query = Payment::query()->with(['invoice.student:id,name_english,student_code', 'receiver:id,name'])->latest('payment_date');
        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }
        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->input('to'));
        }
        $payments = $query->paginate(20)->withQueryString();
        $total = (clone $query)->sum('amount');

        return view('finance.payments', compact('payments', 'total'));
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }

        return $query;
    }

    private function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'student_course_id' => [
                'nullable',
                'integer',
                Rule::exists('student_courses', 'id')->where('student_id', request()->input('student_id')),
            ],
            'fee_structure_id' => ['nullable', 'exists:fee_structures,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', Rule::in(['draft', 'sent'])],
            'discount_type' => ['required', Rule::in(['amount', 'percent'])],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'max:' . (request()->input('discount_type') === 'percent' ? '100' : '99999999')],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function resolveDiscount(array $data, array $items): float
    {
        $subtotal = array_sum(array_map(
            fn (array $item) => (int) $item['quantity'] * (float) $item['unit_price'],
            $items
        ));
        $value = (float) ($data['discount_value'] ?? 0);
        $discount = ($data['discount_type'] ?? 'amount') === 'percent'
            ? round($subtotal * $value / 100, 2)
            : round($value, 2);

        if ($discount > $subtotal) {
            throw ValidationException::withMessages([
                'discount_value' => 'The discount cannot exceed the invoice subtotal.',
            ]);
        }

        return $discount;
    }

    private function validateItems(Request $request): array
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ]);

        return $validated['items'];
    }

    private function saveItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => round((int) $item['quantity'] * (float) $item['unit_price'], 2),
            ]);
        }
    }

    private function nextNumber(): string
    {
        $prefix = 'INV-' . now()->format('Y') . '-';
        // invoice_number is unique centre-wide, so the sequence ignores the campus filter.
        $last = Invoice::withoutGlobalScopes()->where('invoice_number', 'like', $prefix . '%')->max('invoice_number');
        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
