@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-cash-coin text-primary me-1"></i>Finance</h1>
        <div class="page-subtitle">Invoices, fee setup and payment records</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('fee-structures.index') }}" class="btn btn-outline-primary"><i class="bi bi-sliders me-1"></i>Fee Setup</a>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Invoice</a>
    </div>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" href="{{ route('invoices.index') }}">Invoices</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('fee-structures.index') }}">Fee Setup</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('payments.index') }}">Payments</a></li>
</ul>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body py-3">
            <div class="text-muted small">Collected (current filter)</div>
            <div class="fs-5 fw-semibold text-success">${{ number_format($collected, 2) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body py-3">
            <div class="text-muted small">Outstanding balance (all open invoices)</div>
            <div class="fs-5 fw-semibold text-danger">${{ number_format($outstanding, 2) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body py-3">
            <div class="text-muted small">Invoices (current filter)</div>
            <div class="fs-5 fw-semibold">{{ $invoices->total() }}</div>
        </div></div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Invoice no, student name or code" value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach(\App\Models\Invoice::STATUS_LABELS as $k => $label)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Student</label>
                <select name="student_id" class="form-select">
                    <option value="">All students</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" @selected((int) request('student_id') === $s->id)>{{ $s->name_english }} ({{ $s->student_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('invoices.index') }}" class="btn btn-light" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice</th>
                    <th>Student</th>
                    <th>Issued</th>
                    <th>Due</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Balance</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    @php($color = ['draft'=>'secondary','sent'=>'primary','partial'=>'warning','paid'=>'success','overdue'=>'danger','cancelled'=>'dark'][$invoice->status] ?? 'secondary')
                    <tr>
                        <td class="fw-semibold"><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                        <td>{{ $invoice->student->name_english ?? '—' }}<div class="small text-muted">{{ $invoice->student->student_code ?? '' }}</div></td>
                        <td>{{ $invoice->issue_date->format('M d, Y') }}</td>
                        <td>{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="text-end">${{ number_format($invoice->total(), 2) }}</td>
                        <td class="text-end">${{ number_format($invoice->paidAmount(), 2) }}</td>
                        <td class="text-end fw-semibold">${{ number_format($invoice->balance(), 2) }}</td>
                        <td class="text-center"><span class="badge text-bg-{{ $color }}">{{ \App\Models\Invoice::STATUS_LABELS[$invoice->status] ?? $invoice->status }}</span></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary" title="View Invoice"><i class="bi bi-eye me-1"></i>View Invoice</a>
                            @if(!in_array($invoice->status, ['paid','cancelled'], true))
                                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9">
                        <div class="empty-state py-5"><i class="bi bi-receipt"></i><p class="mb-0">No invoices found. Create the first invoice.</p></div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
        <div class="card-footer bg-transparent">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
