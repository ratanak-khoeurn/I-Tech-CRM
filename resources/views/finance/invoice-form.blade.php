@extends('layouts.app')

@section('title', $invoice->exists ? 'Edit Invoice' : 'New Invoice')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-receipt text-primary me-1"></i>{{ $invoice->exists ? 'Edit Invoice ' . $invoice->invoice_number : 'New Invoice' }}</h1>
    <div class="page-subtitle">Line items determine the invoice total; payments are recorded from the invoice page.</div>
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
                <div class="col-md-4">
                    <label class="form-label">Discount</label>
                    <div class="input-group">
                        <select name="discount_type" id="discountType" class="form-select" style="max-width: 105px" aria-label="Discount type">
                            <option value="amount" @selected(old('discount_type', 'amount') === 'amount')>$</option>
                            <option value="percent" @selected(old('discount_type') === 'percent')>%</option>
                        </select>
                        <input type="number" step="0.01" min="0" id="discountValue" name="discount_value" class="form-control @error('discount_value') is-invalid @enderror"
                               value="{{ old('discount_value', $invoice->discount ?? 0) }}" aria-label="Discount value">
                    </div>
                    @error('discount_value')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <div class="form-text" id="discountPreview">Discount amount: $0.00</div>
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
            <div class="d-flex gap-2 align-items-center">
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
                            <th style="width: 46%">Description</th>
                            <th style="width: 12%">Qty</th>
                            <th style="width: 18%">Unit price ($)</th>
                            <th style="width: 16%" class="text-end">Amount</th>
                            <th style="width: 8%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($rows = old('items', $invoice->exists ? $invoice->items->map(fn ($i) => ['description' => $i->description, 'quantity' => $i->quantity, 'unit_price' => $i->unit_price])->all() : []))
                        @forelse($rows as $i => $row)
                            <tr class="item-row">
                                <td><input type="text" name="items[{{ $i }}][description]" class="form-control" value="{{ $row['description'] ?? '' }}" required></td>
                                <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control qty" min="1" value="{{ $row['quantity'] ?? 1 }}" required></td>
                                <td><input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_price]" class="form-control price" value="{{ $row['unit_price'] ?? '' }}" required></td>
                                <td class="text-end row-amount fw-semibold">$0.00</td>
                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-end fw-semibold">Subtotal</td><td class="text-end fw-semibold" id="subtotal">$0.00</td><td></td></tr>
                        <tr><td colspan="3" class="text-end">Discount</td><td class="text-end" id="discountAmount">-$0.00</td><td></td></tr>
                        <tr><td colspan="3" class="text-end fw-semibold">Invoice total</td><td class="text-end fw-semibold" id="invoiceTotal">$0.00</td><td></td></tr>
                    </tfoot>
                </table>
            </div>
            @error('items')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            @error('items.*.description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            @error('items.*.unit_price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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
                    <div class="col-6">
                        <label class="form-label" for="otherFeeQuantity">Quantity</label>
                        <input type="number" class="form-control" id="otherFeeQuantity" min="1" max="10000" value="1" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="otherFeeAmount">Unit price ($) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="otherFeeAmount" step="0.01" min="0" max="99999999" placeholder="0.00" required>
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

    function recalc() {
        var subtotal = 0;
        tbody.querySelectorAll('tr.item-row').forEach(function (tr) {
            var qty = parseFloat(tr.querySelector('.qty').value) || 0;
            var price = parseFloat(tr.querySelector('.price').value) || 0;
            var amount = qty * price;
            subtotal += amount;
            tr.querySelector('.row-amount').textContent = '$' + amount.toFixed(2);
        });
        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        var discountType = document.getElementById('discountType').value;
        var discountValue = parseFloat(document.getElementById('discountValue').value) || 0;
        var discount = discountType === 'percent' ? subtotal * Math.min(discountValue, 100) / 100 : discountValue;
        discount = Math.min(subtotal, discount);
        document.getElementById('discountAmount').textContent = '-$' + discount.toFixed(2);
        document.getElementById('invoiceTotal').textContent = '$' + Math.max(0, subtotal - discount).toFixed(2);
        document.getElementById('discountPreview').textContent = 'Discount amount: $' + discount.toFixed(2);
    }

    function addRow(description, qty, price) {
        var tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML =
            '<td><input type="text" name="items[' + index + '][description]" class="form-control" required></td>' +
            '<td><input type="number" name="items[' + index + '][quantity]" class="form-control qty" min="1" value="1" required></td>' +
            '<td><input type="number" step="0.01" min="0" name="items[' + index + '][unit_price]" class="form-control price" required></td>' +
            '<td class="text-end row-amount fw-semibold">$0.00</td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>';
        index++;
        tbody.appendChild(tr);
        if (description) tr.querySelector('input[name$="[description]"]').value = description;
        if (qty) tr.querySelector('.qty').value = qty;
        if (price) tr.querySelector('.price').value = price;
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
    document.getElementById('discountType').addEventListener('change', recalc);
    document.getElementById('discountValue').addEventListener('input', recalc);

    if (tbody.querySelectorAll('tr.item-row').length === 0) { addRow('', 1, ''); }
    recalc();
})();
</script>
@endpush
@endsection
