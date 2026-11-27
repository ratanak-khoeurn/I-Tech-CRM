@extends('layouts.app')

@section('title', $invoice->exists ? 'Edit Invoice' : 'New Invoice')

@section('content')
@php($methodOptions = \App\Models\Payment::METHODS)
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-receipt text-primary me-1"></i>{{ $invoice->exists ? 'Edit Invoice ' . $invoice->invoice_number : 'New Invoice' }}</h1>
    <div class="page-subtitle">Choose how each fee line is collected, and type its amounts in dollars or riel.</div>
</div>

@php($billingRate = $invoice->rate())
<form method="POST" action="{{ $invoice->exists ? route('invoices.update', $invoice) : route('invoices.store') }}" data-loading>
    @csrf
    @if($invoice->exists) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Invoice Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Student <span class="text-danger">*</span></label>
                    <select name="student_id" id="invoiceStudentId" class="form-select @error('student_id') is-invalid @enderror" required>
                        <option value="">Select student…</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}" @selected((int) old('student_id', $invoice->student_id) === $s->id)>{{ $s->name_english }} ({{ $s->student_code }})</option>
                        @endforeach
                    </select>
                    @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Course Enrollment</label>
                    <select name="student_course_id" id="invoiceStudentCourseId" class="form-select @error('student_course_id') is-invalid @enderror">
                        <option value="">— Not linked to a specific course —</option>
                        @foreach($students as $studentOption)
                            @foreach($studentOption->studentCourses as $studentCourse)
                                <option
                                    value="{{ $studentCourse->id }}"
                                    data-student-id="{{ $studentOption->id }}"
                                    @selected((string) old('student_course_id', $invoice->student_course_id) === (string) $studentCourse->id)
                                >
                                    {{ $studentOption->name_english }} — {{ $studentCourse->course?->name ?? $studentCourse->center_name ?? 'Course enrollment' }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('student_course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Link this invoice to a course to show its payment remark on the student profile.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fee structure (reference)</label>
                    <select name="fee_structure_id" class="form-select">
                        <option value="">— none —</option>
                        @foreach($fees as $fee)
                            <option value="{{ $fee->id }}" @selected((int) old('fee_structure_id', $invoice->fee_structure_id) === $fee->id)>
                                {{ $fee->name }} — {{ $fee->course->name ?? '' }} (${{ number_format((float) $fee->amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Optional link for reporting; add it as a line item below to charge it.</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Issue date <span class="text-danger">*</span></label>
                    <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror"
                           value="{{ old('issue_date', $invoice->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                    @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Due date</label>
                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                           value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}">
                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="draft" @selected(old('status', $invoice->status ?: 'draft') === 'draft')>Draft</option>
                        <option value="sent" @selected(old('status', $invoice->status) === 'sent')>Sent</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" value="{{ old('notes', $invoice->notes) }}" placeholder="Visible on the invoice">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-semibold">Line Items</span>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Each line carries its own payment method, discount and currency.</span>
                <label class="d-flex align-items-center gap-1 mb-0 text-muted small" for="amountCurrency">Default currency</label>
                <div>
                    <select name="amount_currency" id="amountCurrency" class="form-select form-select-sm" style="width: 7.5rem" aria-label="Currency for new amount fields" title="Sets the currency of every line below, and of lines added afterwards">
                        @foreach(\App\Support\Money::CURRENCIES as $code => $sign)
                            <option value="{{ $code }}" @selected(old('amount_currency', 'USD') === $code)>{{ $sign }} {{ $code }}</option>
                        @endforeach
                    </select>
                    @error('amount_currency')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <select id="feePick" class="form-select form-select-sm" style="max-width: 260px;">
                    <option value="">Add from fee setup…</option>
                    @foreach($fees as $fee)
                        <option value="{{ $fee->id }}" data-name="{{ $fee->name }} ({{ $fee->course->name ?? '' }})" data-amount="{{ $fee->amount }}">
                            {{ $fee->name }} — ${{ number_format((float) $fee->amount, 2) }}
                        </option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addFeeLine"><i class="bi bi-plus-lg"></i> Add</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#otherFeeModal"><i class="bi bi-plus-lg"></i> Other fee</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%">Description</th>
                            <th style="width: 5%">Qty</th>
                            <th style="width: 19%">Unit price</th>
                            <th style="width: 19%">Discount</th>
                            <th style="width: 12%">Pay by</th>
                            <th style="width: 20%" class="text-end">Amount</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($rows = old('items', $invoice->exists ? $invoice->items->map(fn ($i) => ['description' => $i->description, 'quantity' => $i->quantity, 'currency' => $i->currency ?: 'USD', 'unit_price' => $i->typedUnitPrice(), 'payment_method' => $i->payment_method ?: 'cash', 'discount' => $i->discountInOwnUnit(), 'discount_type' => $i->discount_type ?: 'amount'])->all() : []))
                        @forelse($rows as $i => $row)
                            @php($rowCurrency = in_array($row['currency'] ?? null, array_keys(\App\Support\Money::CURRENCIES), true) ? $row['currency'] : old('amount_currency', 'USD'))
                            <tr class="item-row">
                                <td><input type="text" name="items[{{ $i }}][description]" class="form-control" value="{{ $row['description'] ?? '' }}" required></td>
                                <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control qty" min="1" value="{{ $row['quantity'] ?? 1 }}" required></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_price]" class="form-control form-control-sm price flex-fill" style="min-width: 4.5rem" value="{{ $row['unit_price'] ?? '' }}" required>
                                        <select name="items[{{ $i }}][currency]" class="form-select form-select-sm cur flex-shrink-0" aria-label="Currency of this amount" style="width: 4.75rem">
                                            @foreach(\App\Support\Money::CURRENCIES as $code => $sign)
                                                <option value="{{ $code }}" @selected($rowCurrency === $code)>{{ $sign }} {{ $code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    {{-- Kept apart instead of one .input-group: Bootstrap pulls each sibling 1px over the previous border, which made the discount field look frameless. --}}
                                    <div class="d-flex gap-1">
                                        <div class="input-group input-group-sm flex-fill">
                                            <span class="input-group-text cur-sign">{{ \App\Support\Money::sign($rowCurrency) }}</span>
                                            <input type="number" step="0.01" min="0" name="items[{{ $i }}][discount]" class="form-control form-control-sm disc flex-fill" style="min-width: 3.5rem" value="{{ $rowCurrency === 'KHR' ? (string) round((float) ($row['discount'] ?? 0)) : number_format((float) ($row['discount'] ?? 0), 2, '.', '') }}" required>
                                        </div>
                                        <select name="items[{{ $i }}][discount_type]" class="form-select form-select-sm disc-type flex-shrink-0" aria-label="Discount unit" style="width: 3.25rem">
                                            @foreach(\App\Models\InvoiceItem::DISCOUNT_UNITS as $unit => $sign)
                                                <option value="{{ $unit }}" @selected(($row['discount_type'] ?? 'amount') === $unit)>{{ $unit === 'amount' ? \App\Support\Money::sign($rowCurrency) : $sign }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <select name="items[{{ $i }}][payment_method]" class="form-select form-select-sm method">
                                        @foreach($methodOptions as $key => $label)
                                            <option value="{{ $key }}" @selected(($row['payment_method'] ?? 'cash') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-end row-amount fw-semibold">$0.00</td>
                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr><td colspan="5" class="text-end fw-semibold">Subtotal (USD)</td><td class="text-end fw-semibold" id="subtotal">$0.00</td><td></td></tr>
                        <tr><td colspan="5" class="text-end">Discount (USD)</td><td class="text-end" id="discountAmount">-$0.00</td><td></td></tr>
                        <tr><td colspan="5" class="text-end fw-semibold">Invoice total (USD)</td><td class="text-end fw-semibold" id="invoiceTotal">$0.00</td><td></td></tr>
                        <tr><td colspan="5" class="text-end small text-muted">Same total in riel at <span id="rateLabel">{{ number_format($billingRate) }}</span> ៛/$</td><td class="text-end small text-muted" id="rielTotal">៛0</td><td></td></tr>
                        <tr><td colspan="5" class="text-end small text-muted">Split of the total by collection method</td><td colspan="2" class="text-end small fw-semibold" id="methodPreview">—</td></tr>
                    </tfoot>
                </table>
            </div>
            @error('items')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            @error('items.*.description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            @error('items.*.unit_price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            @error('items.*.payment_method')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            @error('items.*.discount')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ $invoice->exists ? 'Update Invoice' : 'Create Invoice' }}</button>
        <a href="{{ route('invoices.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>

<div class="modal fade" id="otherFeeModal" tabindex="-1" aria-labelledby="otherFeeModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="otherFeeForm">
            <div class="modal-header">
                <h5 class="modal-title" id="otherFeeModalTitle"><i class="bi bi-plus-circle text-primary me-2"></i>Add Other Fee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="otherFeeDescription">Fee description <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="otherFeeDescription" maxlength="255" placeholder="e.g. Replacement ID card" required>
                </div>
                <div class="row g-3">
                    <div class="col-4">
                        <label class="form-label" for="otherFeeQuantity">Quantity</label>
                        <input type="number" class="form-control" id="otherFeeQuantity" min="1" max="10000" value="1" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="otherFeeAmount">Unit price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" id="otherFeeSign">{{ \App\Support\Money::sign(old('amount_currency', 'USD')) }}</span>
                            <input type="number" class="form-control" id="otherFeeAmount" step="0.01" min="0" max="99999999" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="otherFeeCurrency">Currency</label>
                        <select class="form-select" id="otherFeeCurrency">
                            @foreach(\App\Support\Money::CURRENCIES as $code => $sign)
                                <option value="{{ $code }}" @selected(old('amount_currency', 'USD') === $code)>{{ $sign }} {{ $code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label" for="otherFeeMethod">Pay by</label>
                    <select class="form-select" id="otherFeeMethod">
                        @foreach($methodOptions as $key => $label)
                            <option value="{{ $key }}" @selected($key === 'cash')>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add fee line</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var studentSelect = document.getElementById('invoiceStudentId');
    var studentCourseSelect = document.getElementById('invoiceStudentCourseId');

    function filterStudentCourses() {
        var studentId = studentSelect.value;
        var selectedOption = studentCourseSelect.options[studentCourseSelect.selectedIndex];

        Array.from(studentCourseSelect.options).forEach(function (option) {
            if (!option.dataset.studentId) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            var belongsToStudent = option.dataset.studentId === studentId;
            option.hidden = !belongsToStudent;
            option.disabled = !belongsToStudent;
        });

        if (selectedOption?.dataset.studentId && selectedOption.dataset.studentId !== studentId) {
            studentCourseSelect.value = '';
        }
    }

    studentSelect.addEventListener('change', filterStudentCourses);
    filterStudentCourses();

    var tbody = document.querySelector('#itemsTable tbody');
    var index = tbody.querySelectorAll('tr.item-row').length;
    var methods = @json($methodOptions);
    var methodOptionsHtml = Object.keys(methods).map(function (key) {
        return '<option value="' + key + '">' + methods[key] + '</option>';
    }).join('');
    var units = @json(\App\Models\InvoiceItem::DISCOUNT_UNITS);
    var signs = @json(\App\Support\Money::CURRENCIES);
    var currencyHtml = function (name, selected, extra) {
        return '<select ' + extra + ' name="' + name + '">' + Object.keys(signs).map(function (code) {
            return '<option value="' + code + '"' + (code === selected ? ' selected' : '') + '>' + signs[code] + ' ' + code + '</option>';
        }).join('') + '</select>';
    };
    var rate = @json($billingRate);
    var currencySel = document.getElementById('amountCurrency');
    var otherFeeAmount = document.getElementById('otherFeeAmount');
    var otherFeeCurrency = document.getElementById('otherFeeCurrency');
    var otherFeeSign = document.getElementById('otherFeeSign');

    function signOf(cur) { return cur === 'KHR' ? signs.KHR : signs.USD; }
    function round2(value) { return Math.round(value * 100) / 100; }

    // A field always holds what the cashier typed; every total is worked out in dollars, which is what the ledger stores.
    function toUsd(value, cur) { return cur === 'KHR' ? value / rate : value; }
    function fromUsd(value, cur) { return cur === 'KHR' ? value * rate : value; }
    // Riel changes hands in whole riels, so a riel figure prints without decimals.
    function format(cur, usd) {
        return cur === 'KHR' ? signOf(cur) + Math.round(usd * rate).toLocaleString('en-US') : '$' + usd.toFixed(2);
    }

    function rowCurrency(tr) {
        var sel = tr.querySelector('.cur');
        return sel && sel.value === 'KHR' ? 'KHR' : 'USD';
    }

    function lineAmountUsd(tr) {
        return (parseFloat(tr.querySelector('.qty').value) || 0) * toUsd(parseFloat(tr.querySelector('.price').value) || 0, rowCurrency(tr));
    }

    function recalc() {
        var gross = 0;
        var discount = 0;
        var byMethod = {};
        tbody.querySelectorAll('tr.item-row').forEach(function (tr) {
            var cur = rowCurrency(tr);
            var amount = lineAmountUsd(tr);
            var typed = parseFloat(tr.querySelector('.disc').value) || 0;
            var isPercent = tr.querySelector('.disc-type').value === 'percent';
            var off = Math.min(amount, isPercent ? amount * Math.min(typed, 100) / 100 : toUsd(typed, cur));
            var net = Math.max(0, amount - off);
            gross += amount;
            discount += off;
            tr.querySelector('.row-amount').textContent = cur === 'KHR' ? format(cur, net) + ' ($' + net.toFixed(2) + ')' : format(cur, net);
            var method = tr.querySelector('.method');
            if (method) byMethod[method.value] = (byMethod[method.value] || 0) + net;
        });
        var total = Math.max(0, gross - discount);
        document.getElementById('subtotal').textContent = '$' + gross.toFixed(2);
        document.getElementById('discountAmount').textContent = '-$' + discount.toFixed(2);
        document.getElementById('invoiceTotal').textContent = '$' + total.toFixed(2);
        document.getElementById('rielTotal').textContent = '៛' + Math.round(total * rate).toLocaleString('en-US');

        var parts = Object.keys(byMethod).filter(function (key) { return byMethod[key] > 0; }).map(function (key) {
            return methods[key] + ': $' + byMethod[key].toFixed(2);
        });
        document.getElementById('methodPreview').textContent = parts.length ? parts.join(' · ') : '—';
    }

    // The typed number means dollars (or riel) or percent of that line, so restate it when
    // this row's unit changes instead of silently re-interpreting what the cashier entered.
    function restateDiscount(tr, from, to) {
        var cur = rowCurrency(tr);
        var amount = lineAmountUsd(tr);
        var input = tr.querySelector('.disc');
        var typed = parseFloat(input.value) || 0;
        if (amount <= 0) { return; }
        var usdOff = from === 'percent' ? amount * Math.min(typed, 100) / 100 : toUsd(typed, cur);
        input.value = to === 'percent' ? round2(usdOff / amount * 100) : round2(fromUsd(usdOff, cur));
    }

    function applyRowCurrency(tr) {
        var cur = rowCurrency(tr);
        tr.querySelectorAll('.cur-sign').forEach(function (el) { el.textContent = signOf(cur); });
        var opt = tr.querySelector('.disc-type option[value="amount"]');
        if (opt) { opt.textContent = signOf(cur); }
        // Whole riels only: typing 20000.50 ៛ has no meaning at a counter.
        ['.price', '.disc'].forEach(function (selector) {
            var input = tr.querySelector(selector);
            if (input) { input.step = cur === 'KHR' ? '1' : '0.01'; }
        });
    }

    // Changing a line's currency re-states the amounts already typed on it rather than leaving
    // them to be re-read, because the form submits the typed value with items[i][currency].
    function restateRow(tr, from, to) {
        var factor = to === 'KHR' ? rate : 1 / rate;
        var price = tr.querySelector('.price');
        if (price.value !== '') { price.value = round2((parseFloat(price.value) || 0) * factor); }
        var disc = tr.querySelector('.disc');
        if (tr.querySelector('.disc-type').value === 'amount') {
            disc.value = round2((parseFloat(disc.value) || 0) * factor);
        }
    }

    function setAllRows(to) {
        tbody.querySelectorAll('tr.item-row').forEach(function (tr) {
            var sel = tr.querySelector('.cur');
            if (sel.value !== to) {
                restateRow(tr, sel.value, to);
                sel.value = to;
            }
            sel.dataset.cur = to;
            applyRowCurrency(tr);
        });
    }

    function priceCellHtml(cur) {
        return '<div class="d-flex gap-1">' +
            '<input type="number" step="0.01" min="0" name="items[' + index + '][unit_price]" class="form-control form-control-sm price flex-fill" style="min-width: 4.5rem" required>' +
            currencyHtml('items[' + index + '][currency]', cur, 'class="form-select form-select-sm cur flex-shrink-0" aria-label="Currency of this amount" style="width: 4.75rem"') +
            '</div>';
    }

    function discountCellHtml(cur) {
        return '<div class="d-flex gap-1">' +
            '<div class="input-group input-group-sm flex-fill">' +
            '<span class="input-group-text cur-sign">' + signOf(cur) + '</span>' +
            '<input type="number" step="0.01" min="0" name="items[' + index + '][discount]" class="form-control form-control-sm disc flex-fill" style="min-width: 3.5rem" value="0" required>' +
            '</div>' +
            '<select name="items[' + index + '][discount_type]" class="form-select form-select-sm disc-type flex-shrink-0" aria-label="Discount unit" style="width: 3.25rem">' +
            Object.keys(units).map(function (key) {
                return '<option value="' + key + '">' + (key === 'amount' ? signOf(cur) : units[key]) + '</option>';
            }).join('') +
            '</select>' +
            '</div>';
    }

    function addRow(description, qty, price, method, cur) {
        cur = cur === 'KHR' ? 'KHR' : 'USD';
        var tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML =
            '<td><input type="text" name="items[' + index + '][description]" class="form-control" required></td>' +
            '<td><input type="number" name="items[' + index + '][quantity]" class="form-control qty" min="1" value="1" required></td>' +
            '<td>' + priceCellHtml(cur) + '</td>' +
            '<td>' + discountCellHtml(cur) + '</td>' +
            '<td><select name="items[' + index + '][payment_method]" class="form-select form-select-sm method">' + methodOptionsHtml + '</select></td>' +
            '<td class="text-end row-amount fw-semibold">$0.00</td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>';
        index++;
        tbody.appendChild(tr);
        if (description) tr.querySelector('input[name$="[description]"]').value = description;
        if (qty) tr.querySelector('.qty').value = qty;
        if (price) tr.querySelector('.price').value = price;
        tr.querySelector('.method').value = method || 'cash';
        var unit = tr.querySelector('.disc-type');
        unit.dataset.unit = unit.value;
        tr.querySelector('.cur').dataset.cur = cur;
        applyRowCurrency(tr);
        recalc();
    }

    var otherFeeModal = document.getElementById('otherFeeModal');
    var otherFeeForm = document.getElementById('otherFeeForm');
    otherFeeForm.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!otherFeeForm.reportValidity()) return;

        addRow(
            document.getElementById('otherFeeDescription').value.trim(),
            document.getElementById('otherFeeQuantity').value,
            otherFeeAmount.value,
            document.getElementById('otherFeeMethod').value,
            otherFeeCurrency.value,
        );
        otherFeeForm.reset();
        document.getElementById('otherFeeQuantity').value = '1';
        bootstrap.Modal.getOrCreateInstance(otherFeeModal).hide();
    });
    otherFeeModal.addEventListener('hidden.bs.modal', function () { otherFeeForm.reset(); });

    // The modal is a second place to type an amount, so it follows the same rule as a line.
    function syncModalFields(from) {
        var to = otherFeeCurrency.value;
        otherFeeSign.textContent = signOf(to);
        otherFeeAmount.step = to === 'KHR' ? '1' : '0.01';
        if (from && from !== to) {
            var typed = parseFloat(otherFeeAmount.value);
            if (!isNaN(typed)) { otherFeeAmount.value = round2(typed * (to === 'KHR' ? rate : 1 / rate)); }
        }
        otherFeeCurrency.dataset.cur = to;
    }
    otherFeeCurrency.addEventListener('change', function () { syncModalFields(otherFeeCurrency.dataset.cur); });
    syncModalFields(null);

    document.getElementById('addFeeLine').addEventListener('click', function () {
        var sel = document.getElementById('feePick');
        var opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) { return; }
        // Fee setup stores dollars, so quote it in the currency the cashier is working in.
        var cur = currencySel.value;
        var price = opt.dataset.amount ? (cur === 'KHR' ? Math.round(parseFloat(opt.dataset.amount) * rate) : opt.dataset.amount) : '';
        addRow(opt.dataset.name, 1, price, 'cash', cur);
        sel.selectedIndex = 0;
    });

    tbody.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-row');
        if (btn) { btn.closest('tr').remove(); recalc(); }
    });
    tbody.addEventListener('input', recalc);
    tbody.addEventListener('change', recalc);
    tbody.addEventListener('change', function (e) {
        var sel = e.target.closest('.disc-type');
        if (!sel) { return; }
        var from = sel.dataset.unit || 'amount';
        if (from !== sel.value) { restateDiscount(sel.closest('tr'), from, sel.value); }
        sel.dataset.unit = sel.value;
        recalc();
    });
    tbody.addEventListener('change', function (e) {
        var sel = e.target.closest('.cur');
        if (!sel) { return; }
        var from = sel.dataset.cur || 'USD';
        if (from !== sel.value) { restateRow(sel.closest('tr'), from, sel.value); }
        sel.dataset.cur = sel.value;
        applyRowCurrency(sel.closest('tr'));
        recalc();
    });

    if (currencySel) {
        currencySel.addEventListener('change', function () {
            setAllRows(currencySel.value);
            otherFeeCurrency.value = currencySel.value;
            syncModalFields(otherFeeCurrency.dataset.cur);
            recalc();
        });
    }

    tbody.querySelectorAll('.disc-type').forEach(function (sel) { sel.dataset.unit = sel.value; });
    tbody.querySelectorAll('tr.item-row').forEach(function (tr) {
        var sel = tr.querySelector('.cur');
        if (sel) { sel.dataset.cur = sel.value; }
        applyRowCurrency(tr);
    });

    if (tbody.querySelectorAll('tr.item-row').length === 0) { addRow('', 1, '', 'cash', currencySel.value); }
    recalc();
})();
</script>
@endpush
@endsection
