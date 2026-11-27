@extends('layouts.app')

@section('title', $invoice->exists ? 'Edit Invoice' : 'New Invoice')

@section('content')
@php($methodOptions = \App\Models\Payment::METHODS)
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-receipt text-primary me-1"></i>{{ $invoice->exists ? 'Edit Invoice ' . $invoice->invoice_number : 'New Invoice' }}</h1>
    <div class="page-subtitle">Choose how each fee line is collected; the invoice shows the method per line.</div>
</div>

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
            <div class="text-muted small"><i class="bi bi-info-circle me-1"></i>Each line carries its own payment method and discount ($ or %).</div>
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
                            <th style="width: 27%">Description</th>
                            <th style="width: 7%">Qty</th>
                            <th style="width: 13%">Unit price</th>
                            <th style="width: 17%">Discount</th>
                            <th style="width: 15%">Pay by</th>
                            <th style="width: 16%" class="text-end">Amount</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($rows = old('items', $invoice->exists ? $invoice->items->map(fn ($i) => ['description' => $i->description, 'quantity' => $i->quantity, 'unit_price' => $i->unit_price, 'payment_method' => $i->payment_method ?: 'cash', 'discount' => $i->discountInOwnUnit(), 'discount_type' => $i->discount_type ?: 'amount'])->all() : []))
                        @forelse($rows as $i => $row)
                            <tr class="item-row">
                                <td><input type="text" name="items[{{ $i }}][description]" class="form-control" value="{{ $row['description'] ?? '' }}" required></td>
                                <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control qty" min="1" value="{{ $row['quantity'] ?? 1 }}" required></td>
                                <td><input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_price]" class="form-control price" value="{{ $row['unit_price'] ?? '' }}" required></td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" min="0" name="items[{{ $i }}][discount]" class="form-control disc" value="{{ number_format((float) ($row['discount'] ?? 0), 2, '.', '') }}" required>
                                        <select name="items[{{ $i }}][discount_type]" class="form-select form-select-sm disc-type" aria-label="Discount unit">
                                            @foreach(\App\Models\InvoiceItem::DISCOUNT_UNITS as $unit => $sign)
                                                <option value="{{ $unit }}" @selected(($row['discount_type'] ?? 'amount') === $unit)>{{ $sign }}</option>
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
                        <tr><td colspan="5" class="text-end fw-semibold">Subtotal</td><td class="text-end fw-semibold" id="subtotal">$0.00</td><td></td></tr>
                        <tr><td colspan="5" class="text-end">Discount</td><td class="text-end" id="discountAmount">-$0.00</td><td></td></tr>
                        <tr><td colspan="5" class="text-end fw-semibold">Invoice total</td><td class="text-end fw-semibold" id="invoiceTotal">$0.00</td><td></td></tr>
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
                        <label class="form-label" for="otherFeeAmount">Unit price ($) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="otherFeeAmount" step="0.01" min="0" max="99999999" placeholder="0.00" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="otherFeeMethod">Pay by</label>
                        <select class="form-select" id="otherFeeMethod">
                            @foreach($methodOptions as $key => $label)
                                <option value="{{ $key }}" @selected($key === 'cash')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
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
    var unitOptionsHtml = Object.keys(units).map(function (key) {
        return '<option value="' + key + '">' + units[key] + '</option>';
    }).join('');

    function lineAmount(tr) {
        return (parseFloat(tr.querySelector('.qty').value) || 0) * (parseFloat(tr.querySelector('.price').value) || 0);
    }

    function recalc() {
        var gross = 0;
        var discount = 0;
        var byMethod = {};
        tbody.querySelectorAll('tr.item-row').forEach(function (tr) {
            var amount = lineAmount(tr);
            var typed = parseFloat(tr.querySelector('.disc').value) || 0;
            var isPercent = tr.querySelector('.disc-type').value === 'percent';
            var off = Math.min(amount, isPercent ? amount * Math.min(typed, 100) / 100 : typed);
            var net = Math.max(0, amount - off);
            gross += amount;
            discount += off;
            tr.querySelector('.row-amount').textContent = '$' + net.toFixed(2);
            var method = tr.querySelector('.method');
            if (method) byMethod[method.value] = (byMethod[method.value] || 0) + net;
        });
        document.getElementById('subtotal').textContent = '$' + gross.toFixed(2);
        document.getElementById('discountAmount').textContent = '-$' + discount.toFixed(2);
        document.getElementById('invoiceTotal').textContent = '$' + Math.max(0, gross - discount).toFixed(2);

        var parts = Object.keys(byMethod).filter(function (key) { return byMethod[key] > 0; }).map(function (key) {
            return methods[key] + ': $' + byMethod[key].toFixed(2);
        });
        document.getElementById('methodPreview').textContent = parts.length ? parts.join(' · ') : '—';
    }

    // The typed number means dollars or percent of that line, so restate it when this
    // row's unit changes instead of silently re-interpreting what the cashier entered.
    function restateDiscount(tr, from, to) {
        var amount = lineAmount(tr);
        var input = tr.querySelector('.disc');
        var typed = parseFloat(input.value) || 0;
        if (amount <= 0) { return; }
        var dollars = from === 'percent' ? amount * Math.min(typed, 100) / 100 : typed;
        input.value = to === 'percent' ? +(dollars / amount * 100).toFixed(2) : dollars.toFixed(2);
    }

    function discountCellHtml() {
        return '<div class="input-group input-group-sm">' +
            '<input type="number" step="0.01" min="0" name="items[' + index + '][discount]" class="form-control disc" value="0" required>' +
            '<select name="items[' + index + '][discount_type]" class="form-select form-select-sm disc-type" aria-label="Discount unit">' + unitOptionsHtml + '</select>' +
            '</div>';
    }

    function addRow(description, qty, price, method) {
        var tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML =
            '<td><input type="text" name="items[' + index + '][description]" class="form-control" required></td>' +
            '<td><input type="number" name="items[' + index + '][quantity]" class="form-control qty" min="1" value="1" required></td>' +
            '<td><input type="number" step="0.01" min="0" name="items[' + index + '][unit_price]" class="form-control price" required></td>' +
            '<td>' + discountCellHtml() + '</td>' +
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
            document.getElementById('otherFeeAmount').value,
            document.getElementById('otherFeeMethod').value,
        );
        otherFeeForm.reset();
        document.getElementById('otherFeeQuantity').value = '1';
        bootstrap.Modal.getOrCreateInstance(otherFeeModal).hide();
    });
    otherFeeModal.addEventListener('hidden.bs.modal', function () { otherFeeForm.reset(); });

    document.getElementById('addFeeLine').addEventListener('click', function () {
        var sel = document.getElementById('feePick');
        var opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) { return; }
        addRow(opt.dataset.name, 1, opt.dataset.amount);
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
    tbody.querySelectorAll('.disc-type').forEach(function (sel) { sel.dataset.unit = sel.value; });

    if (tbody.querySelectorAll('tr.item-row').length === 0) { addRow('', 1, ''); }
    recalc();
})();
</script>
@endpush
@endsection
