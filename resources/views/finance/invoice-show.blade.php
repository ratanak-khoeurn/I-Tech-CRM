@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
@php($color = ['draft'=>'secondary','sent'=>'primary','partial'=>'warning','paid'=>'success','overdue'=>'danger','cancelled'=>'dark'][$invoice->status] ?? 'secondary')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-receipt text-primary me-1"></i>{{ $invoice->invoice_number }}
            <span class="badge text-bg-{{ $color }} align-middle ms-1">{{ \App\Models\Invoice::STATUS_LABELS[$invoice->status] ?? $invoice->status }}</span>
        </h1>
        <div class="page-subtitle">Issued {{ $invoice->issue_date->format('M d, Y') }} · Due {{ $invoice->due_date?->format('M d, Y') ?? '—' }}</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-outline-secondary" onclick="printFinancialDocument('both')"><i class="bi bi-printer me-1"></i>Print Invoice &amp; Receipt</button>
        @if($invoice->status === 'draft')
            <form method="POST" action="{{ route('invoices.status', $invoice) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="sent">
                <button class="btn btn-outline-primary"><i class="bi bi-send me-1"></i>Mark as sent</button>
            </form>
        @endif
        @if(!in_array($invoice->status, ['paid', 'cancelled'], true))
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        @if($invoice->status !== 'cancelled')
            <form method="POST" action="{{ route('invoices.status', $invoice) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="btn btn-outline-danger"
                        data-confirm="Void invoice {{ $invoice->invoice_number }}? Its recorded payment history will be retained.">
                    <i class="bi bi-slash-circle me-1"></i>Void invoice
                </button>
            </form>
        @endif
        @if(!$invoice->payments->count())
            <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" onsubmit="return confirm('You are about to delete **invoice** {{ $invoice->invoice_number }}. This action cannot be undone. Would you like to proceed?');">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>
        @endif
    </div>
</div>

@if($invoice->status === 'cancelled')
    <div class="alert alert-danger"><i class="bi bi-slash-circle me-1"></i><strong>This invoice is void.</strong> Recorded payment history has been retained.</div>
@endif

<?php
    $invoiceSubtotal = (float) $invoice->items->sum('amount');
    $invoiceDiscount = (float) $invoice->items->sum('discount');
    $lastPayment = $invoice->payments->sortByDesc('payment_date')->first();
    $cashierName = optional(auth()->user())->name ?: ($lastPayment?->receiver?->name ?? '');
    $breakdown = $invoice->methodBreakdown();
    $centerName = \App\Models\Setting::get('center_name', 'I-Tech Media Center');
    $centerAddress = \App\Models\Setting::get('center_address', '');
    $centerPhone = \App\Models\Setting::get('center_phone', '');
    $logoPath = \App\Models\Setting::get('center_logo');
    $logoUrl = $logoPath ? asset('storage/' . $logoPath) : asset('assets/logo.png');
    $amountWords = function (float $amount): string {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $spell = function (int $number) use (&$spell, $ones, $tens): string {
            if ($number < 20) return $ones[$number];
            if ($number < 100) return $tens[intdiv($number, 10)] . ($number % 10 ? ' ' . $ones[$number % 10] : '');
            if ($number < 1000) return $ones[intdiv($number, 100)] . ' Hundred' . ($number % 100 ? ' ' . $spell($number % 100) : '');
            foreach ([1000000 => 'Million', 1000 => 'Thousand'] as $size => $label) {
                if ($number >= $size) return $spell(intdiv($number, $size)) . ' ' . $label . ($number % $size ? ' ' . $spell($number % $size) : '');
            }
            return '';
        };
        $cents = (int) round(($amount - floor($amount)) * 100);
        return ($amount < 0 ? 'Minus ' : '') . ($spell((int) abs($amount)) ?: 'Zero') . ' Dollars' . ($cents ? ' and ' . $spell($cents) . ' Cents' : '') . ' Only';
    };
    $documentKinds = [
        'invoice' => ['title' => 'INVOICE', 'date' => $invoice->issue_date, 'dateLabel' => 'Date:'],
        'receipt' => ['title' => 'OFFICIAL RECEIPT', 'date' => $lastPayment?->payment_date ?? $invoice->issue_date, 'dateLabel' => 'Paid date:'],
    ];
?>

@foreach($documentKinds as $kind => $document)
<section class="invoice-print" id="{{ $kind }}Print">
    <div class="certificate-border">
        <header class="invoice-print-header">
            <div class="school-contact">
                <img src="{{ $logoUrl }}" alt="{{ $centerName }} logo" class="invoice-print-logo">
                <div class="school-name">{{ $centerName }}</div>
                @if($centerAddress)<div>{{ $centerAddress }}</div>@endif
                @if($centerPhone)<div>{{ $centerPhone }}</div>@endif
            </div>
            <div class="invoice-meta">
                {{-- Both sheets carry the same two figures: the centre numbers nothing but the invoice, so the receipt quotes it as "Invoice No" too. --}}
                <div><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</div>
                <div><strong>Paid Date:</strong> {{ $document['date']->format('d-M-Y') }}</div>
            </div>
        </header>

        <div class="document-title">
            <h1>{{ $document['title'] }}</h1>
            @if($invoice->status === 'cancelled')<div class="void-stamp">VOIDED</div>@endif
            <div class="title-stars">**************************************</div>
        </div>

        <div class="student-line"><strong>Student Name:</strong> {{ $invoice->student->name_english ?? '—' }}</div>

        <table class="invoice-print-table">
            <thead>
                <tr><th class="number">No</th><th>Item Description</th><th class="method">Payment Method</th><th class="money">Amount</th><th class="discount">Discount</th><th class="money">Net Amount</th></tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                    <tr>
                        <td class="number">{{ $index + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="method">{{ $item->methodLabel() }}</td>
                        {{-- The sheet repeats the currency the fee was billed in, so a riel line is not read as dollars. --}}
                        <td class="money">{{ $item->typedLabel($item->typedAmount()) }}</td>
                        <td class="money">{{ (float) $item->discount > 0 ? '-' . $item->discountLabel() : '—' }}</td>
                        <td class="money">{{ $item->netLabel() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="invoice-totals">
            <tbody>
                <tr><td>Sub Total:</td><td class="sub-total-value">${{ number_format($invoiceSubtotal, 2) }}</td></tr>
                @if($invoiceDiscount > 0)
                    <tr><td>Discount:</td><td class="total-value">-${{ number_format($invoiceDiscount, 2) }}</td></tr>
                @endif
                <tr><td>VAT 10%:</td><td class="total-value"></td></tr>
                <tr class="grand-total"><td>Grand Total:</td><td class="total-value">${{ number_format($invoice->total(), 2) }}</td></tr>
                {{-- Cambodian receipts quote the riel value at the rate this invoice was billed at, not today's. --}}
                <tr><td>At {{ number_format($invoice->rate()) }} ៛/USD:</td><td class="total-value">{{ \App\Support\Money::rielLabel($invoice->total(), $invoice->rate()) }}</td></tr>
            </tbody>
        </table>

        <div class="payment-method">
            <strong>Payment By:</strong>
            @php($amounts = collect($breakdown)->map(fn (array $row) => $row['label'] . ' ($' . number_format($kind === 'receipt' ? $row['paid'] : $row['due'], 2) . ')')->implode(' · '))
            {{ $amounts !== '' ? $amounts : '—' }}
        </div>

        <div class="invoice-note"><strong>Note</strong><br>{{ $invoice->notes ?: '* All payments are not refundable or transferable' }}</div>
        <footer class="signature-area">
            <div><span>Payer’s Signature & Name</span><div class="signature-line">{{ $invoice->student->name_english ?? '' }}</div></div>
            <div><span>Cashier’s Signature</span><div class="signature-line">{{ $cashierName }}</div></div>
        </footer>
    </div>
</section>
@endforeach

<div class="row g-3 mb-3">
    <div class="col-md-5">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small mb-1">Billed to</div>
            <div class="fw-semibold">{{ $invoice->student->name_english ?? '—' }}</div>
            <div class="small">{{ $invoice->student->student_code ?? '' }}</div>
            <div class="small">{{ $invoice->student->phone ?? '' }} {{ $invoice->student->email ? '· ' . $invoice->student->email : '' }}</div>
            @if($invoice->student?->studentCourses?->first())
                <div class="small text-muted mt-1">{{ $invoice->student->studentCourses->first()->course->name ?? '' }}</div>
            @endif
            @if($invoice->feeStructure)
                <div class="small text-muted">Fee structure: {{ $invoice->feeStructure->name }}</div>
            @endif
        </div></div>
    </div>
    <div class="col-md-7">
        <div class="card h-100"><div class="card-body">
            <div class="row text-center g-2">
                <div class="col"><div class="text-muted small">Total</div><div class="fs-5 fw-semibold">${{ number_format($invoice->total(), 2) }}</div></div>
                <div class="col"><div class="text-muted small">Paid</div><div class="fs-5 fw-semibold text-success">${{ number_format($invoice->paidAmount(), 2) }}</div></div>
                <div class="col"><div class="text-muted small">Balance</div><div class="fs-5 fw-semibold text-danger">${{ number_format($invoice->balance(), 2) }}</div></div>
                <div class="col"><div class="text-muted small">Discount</div><div class="fs-5 fw-semibold">${{ number_format((float) $invoice->discount, 2) }}</div></div>
            </div>
            <div class="text-muted small text-center mt-2">
                At {{ number_format($invoice->rate()) }} ៛/USD: total {{ \App\Support\Money::rielLabel($invoice->total(), $invoice->rate()) }}
                · balance {{ \App\Support\Money::rielLabel($invoice->balance(), $invoice->rate()) }}
            </div>
        </div></div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-white fw-semibold">Line items <span class="text-muted small fw-normal ms-1">each amount is shown in the currency that fee was billed in</span></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr><th>Description</th><th class="text-center">Qty</th><th class="text-end">Unit price</th><th class="text-end">Discount</th><th>Pay by</th><th class="text-end">Amount</th></tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">{{ $item->unitPriceLabel() }}</td>
                        <td class="text-end {{ (float) $item->discount > 0 ? 'text-success' : 'text-muted' }}">
                            {{ (float) $item->discount > 0 ? '-' . $item->discountLabel() : '—' }}
                        </td>
                        <td><span class="badge text-bg-light border">{{ $item->methodLabel() }}</span></td>
                        <td class="text-end">
                            {{ $item->netLabel() }}
                            @if($item->isRiel())
                                <div class="text-muted small">${{ number_format($item->net(), 2) }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-3">No line items.</td></tr>
                @endforelse
            </tbody>
            @if(count($breakdown) > 1)
                <tfoot class="border-top">
                    <tr>
                        <td colspan="6" class="pt-2">
                            <div class="d-flex flex-wrap gap-3 small">
                                @foreach($breakdown as $row)
                                    <div>
                                        <span class="text-muted">{{ $row['label'] }}:</span>
                                        <span class="fw-semibold">${{ number_format($row['due'], 2) }}</span>
                                        @if($row['paid'] > 0)
                                            <span class="text-success">paid ${{ number_format($row['paid'], 2) }}</span>
                                        @endif
                                        @if($row['balance'] > 0.009)
                                            <span class="text-danger">due ${{ number_format($row['balance'], 2) }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    @if($invoice->notes)
        <div class="card-footer bg-white small text-muted">{{ $invoice->notes }}</div>
    @endif
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Payments</span>
        @if(!in_array($invoice->status, ['cancelled'], true) && $invoice->balance() > 0)
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="bi bi-cash me-1"></i>Record payment</button>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Method</th><th>Reference</th><th>Received by</th><th class="text-end">Amount</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                        <td>{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</td>
                        <td>{{ $payment->reference ?: '—' }}</td>
                        <td>{{ $payment->receiver->name ?? '—' }}</td>
                        <td class="text-end fw-semibold">${{ number_format((float) $payment->amount, 2) }}
                            <div class="small text-muted fw-normal">{{ $payment->rielLabel() }} @ {{ number_format($payment->rate()) }}</div>
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('payments.destroy', $payment) }}" class="d-inline" onsubmit="return confirm('You are about to delete this **payment**. This action cannot be undone. Would you like to proceed?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-3">No payments recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(!in_array($invoice->status, ['cancelled'], true) && $invoice->balance() > 0)
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('invoices.payments.store', $invoice) }}" class="modal-content" data-loading>
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Record payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border small mb-3">
                    Outstanding balance: <strong>${{ number_format($invoice->balance(), 2) }}</strong>
                    <span class="text-muted">({{ \App\Support\Money::rielLabel($invoice->balance()) }})</span>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Payment date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Amount (<span class="pay-sign">{{ \App\Support\Money::sign(old('currency')) }}</span>) <span class="text-danger">*</span></label>
                        <div class="d-flex gap-1">
                            <div class="input-group flex-fill">
                                <span class="input-group-text pay-sign">{{ \App\Support\Money::sign(old('currency')) }}</span>
                                <input type="number" step="0.01" min="0.01" max="{{ $invoice->balance() }}" name="amount" class="form-control pay-amount" value="{{ old('amount', $invoice->balance()) }}" required>
                            </div>
                            <select name="currency" class="form-select pay-currency flex-shrink-0" style="width: 5.5rem" aria-label="Currency of the amount typed">
                                @foreach(\App\Support\Money::CURRENCIES as $code => $sign)
                                    <option value="{{ $code }}" @selected(old('currency', 'USD') === $code)>{{ $code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-text pay-hint">—</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Collected by</label>
                        <div class="small fw-semibold" id="splitPreview">—</div>
                        <div class="form-text">Follows the fee lines this payment settles, so no method to choose here.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="Receipt / txn no">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save payment</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('paymentModal')).show();
        });
    </script>
    @endpush
@endif

@push('scripts')
<script>
(function () {
    var owed = @json(collect($breakdown)->map(fn (array $row) => ['label' => $row['label'], 'balance' => max(0, $row['balance'])])->values()->all());
    var rate = @json(\App\Support\Money::rate());
    var balance = @json(round((float) $invoice->balance(), 2));
    var amountInput = document.querySelector('#paymentModal .pay-amount');
    var currencySel = document.querySelector('#paymentModal .pay-currency');
    var preview = document.getElementById('splitPreview');
    if (!amountInput || !preview) { return; }

    function inRiel() { return currencySel && currencySel.value === 'KHR'; }
    function usdValue() { return inRiel() ? (parseFloat(amountInput.value) || 0) / rate : (parseFloat(amountInput.value) || 0); }

    function render() {
        var left = usdValue();
        var parts = [];
        owed.forEach(function (channel) {
            if (left <= 0 || channel.balance <= 0) { return; }
            var take = Math.min(left, channel.balance);
            left = Math.round((left - take) * 100) / 100;
            parts.push(channel.label + ': $' + take.toFixed(2));
        });
        preview.textContent = parts.length ? parts.join('  ·  ') : '—';

        var usd = usdValue();
        document.querySelector('#paymentModal .pay-hint').textContent = inRiel()
            ? '= $' + usd.toFixed(2) + ' at ' + rate.toLocaleString('en-US') + ' ៛/USD'
            : '≈ ៛' + Math.round(usd * rate).toLocaleString('en-US') + ' at ' + rate.toLocaleString('en-US') + ' ៛/USD';
    }

    function syncCurrency(from) {
        var sign = inRiel() ? '៛' : '$';
        document.querySelectorAll('#paymentModal .pay-sign').forEach(function (el) { el.textContent = sign; });
        amountInput.max = inRiel() ? Math.ceil(balance * rate) : balance;
        if (from && from !== currencySel.value) {
            var typed = parseFloat(amountInput.value);
            if (!isNaN(typed)) { amountInput.value = inRiel() ? Math.round(typed * rate) : Math.round(typed / rate * 100) / 100; }
        }
    }

    var enteredIn = currencySel ? currencySel.value : 'USD';
    if (currencySel) {
        currencySel.addEventListener('change', function () {
            syncCurrency(enteredIn);
            enteredIn = currencySel.value;
            render();
        });
    }

    amountInput.addEventListener('input', render);
    syncCurrency(null);
    render();
})();
</script>
@endpush
@endif
@push('styles')
<style>
    .invoice-print { display: none; }
    @media print {
        /* No printer prints to the edge, so the sheet is sized to the printable area instead of
           the whole page: a border-to-border 210×297 box spills onto a second sheet (or the driver
           shrinks it) on every dialog except Margins: None. */
        @page { size: A4 portrait; margin: 8mm; }
        body { background: #fff !important; }
        .sidebar, .topbar, .sidebar-backdrop, .mb-3, .row.g-3.mb-3, .card, .modal, .toast-container, .invoice-print-controls { display: none !important; }
        .main-area, .app-shell, .content, main { display: block !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .invoice-print { display: none !important; color: #111827; font: 9pt Arial, Helvetica, sans-serif; }
        body.print-invoice #invoicePrint, body.print-receipt #receiptPrint, body.print-both .invoice-print { display: block !important; }
        /* Each document is a full A4 sheet, so the invoice page ends before the receipt starts. */
        body.print-both #invoicePrint { break-after: page; page-break-after: always; }
        /* No printed frame: the centre wants plain ink, so the class only boxes the sheet. */
        .certificate-border { position: relative; width: 100%; min-height: 273mm; padding: 13mm 9mm 12mm; overflow: hidden; display: flex; flex-direction: column; }
        .invoice-print-header { display: table; width: 100%; min-height: 37mm; }
        .school-contact, .invoice-meta { display: table-cell; vertical-align: top; }
        .school-contact { width: 62%; line-height: 1.55; }
        .invoice-meta { width: 38%; text-align: right; line-height: 1.7; font-size: 8pt; }
        .invoice-print-logo { display: block; width: 22mm; height: 22mm; object-fit: contain; margin-bottom: 1mm; }
        .school-name { font-weight: bold; color: #173f80; text-decoration: underline; margin-bottom: 2mm; }
        .document-title { text-align: center; margin: 4mm 0 10mm; }
        .document-title h1 { margin: 0; font-size: 15pt; font-weight: bold; }
        .void-stamp { display: inline-block; margin-top: 2mm; padding: 1mm 4mm; border: 1mm solid #b91c1c; color: #b91c1c; font-size: 15pt; font-weight: bold; letter-spacing: 1mm; transform: rotate(-5deg); }
        .title-stars { margin-top: 1mm; font-size: 8pt; font-weight: bold; letter-spacing: .2mm; }
        .student-line { margin: 0 0 6mm; }
        .invoice-print-table { width: 100%; border-collapse: collapse; font-size: 8pt; }
        .invoice-print-table th, .invoice-print-table td { border: .3mm solid #222; padding: 2mm 1.5mm; }
        .invoice-print-table th { text-align: center; background: #f1f1f1 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .invoice-print-table .number { width: 8mm; text-align: center; }
        .invoice-print-table .money { width: 27mm; text-align: right; white-space: nowrap; }
        .invoice-print-table .discount { width: 25mm; text-align: center; }
        .invoice-print-table .method { width: 25mm; text-align: center; white-space: nowrap; }
        .invoice-print-table th.discount { width: 20mm; }
        .invoice-totals { width: 65mm; margin: 0 0 0 auto; border-collapse: collapse; font-size: 8pt; }
        .invoice-totals td { border: 0 !important; padding: 1mm 1.5mm; }
        .invoice-totals td.total-value { border: .3mm solid #222 !important; }
        .invoice-totals td.sub-total-value { border-top: none !important; border-left: .3mm solid #222 !important; border-right: .3mm solid #222 !important; border-bottom: .3mm solid #222 !important;}
        .invoice-totals td:first-child { text-align: right; }
        .invoice-totals td:last-child { width: 27mm; text-align: right; white-space: nowrap; }
        .invoice-totals .grand-total td { padding-top: 1.5mm; font-weight: bold; }
        .payment-method { margin-top: 2mm; font-size: 8pt; }
        .invoice-lower { width: 100%; min-height: 18mm; margin-top: 3mm; }
        .amount-words { width: 100%; font-size: 8pt; line-height: 1.7; padding-top: 1mm; }
        .invoice-note { margin-top: 5mm; line-height: 1.8; }
        /* Stays at the foot of a short invoice and pushes the sheet taller instead of overlapping
           the fee table when an invoice has many lines. */
        .signature-area { margin-top: auto; display: flex; justify-content: space-between; gap: 20mm; font-size: 8pt; }
        .signature-area > div { flex: 1 1 0; min-width: 0; text-align: center; }
        .signature-line { width: 100%; margin-top: 20mm; border-top: .3mm dashed #555; text-align: center; padding-top: 1mm; min-height: 5mm; }
    }
</style>
@endpush

@push('scripts')
<script>
    function printFinancialDocument(type) {
        document.body.classList.remove('print-invoice', 'print-receipt', 'print-both');
        document.body.classList.add('print-' + type);
        window.print();
    }
    window.addEventListener('afterprint', function () {
        document.body.classList.remove('print-invoice', 'print-receipt', 'print-both');
    });
</script>
@endpush
@endsection
