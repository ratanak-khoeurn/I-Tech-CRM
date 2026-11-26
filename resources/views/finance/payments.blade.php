@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-cash-coin text-primary me-1"></i>Payments</h1>
    <div class="page-subtitle">All payments received across invoices</div>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link" href="{{ route('invoices.index') }}">Invoices</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('fee-structures.index') }}">Fee Setup</a></li>
    <li class="nav-item"><a class="nav-link active" href="{{ route('payments.index') }}">Payments</a></li>
</ul>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card h-100"><div class="card-body py-3">
            <div class="text-muted small">Total received (current filter)</div>
            <div class="fs-5 fw-semibold text-success">${{ number_format((float) $total, 2) }}</div>
        </div></div>
    </div>
    <div class="col-md-8">
        <div class="card h-100"><div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Method</label>
                    <select name="method" class="form-select">
                        <option value="">All methods</option>
                        @foreach(\App\Models\Payment::METHODS as $k => $label)
                            <option value="{{ $k }}" @selected(request('method') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">From</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                    <a href="{{ route('payments.index') }}" class="btn btn-light" title="Reset"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th><th>Invoice</th><th>Student</th><th>Method</th><th>Reference</th>
                    <th>Received by</th><th class="text-end">Amount</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                        <td><a href="{{ route('invoices.show', $payment->invoice_id) }}">{{ $payment->invoice->invoice_number ?? '#' . $payment->invoice_id }}</a></td>
                        <td>{{ $payment->invoice->student->name_english ?? '—' }}</td>
                        <td>{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</td>
                        <td>{{ $payment->reference ?: '—' }}</td>
                        <td>{{ $payment->receiver->name ?? '—' }}</td>
                        <td class="text-end fw-semibold">${{ number_format((float) $payment->amount, 2) }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('payments.destroy', $payment) }}" class="d-inline" onsubmit="return confirm('You are about to delete this **payment**. This action cannot be undone. Would you like to proceed?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">
                        <div class="empty-state py-5"><i class="bi bi-cash"></i><p class="mb-0">No payments recorded yet.</p></div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
        <div class="card-footer bg-transparent">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
