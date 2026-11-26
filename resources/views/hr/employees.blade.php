@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-person-vcard text-primary me-1"></i>Employees</h1>
        <div class="page-subtitle">Staff records, positions and ID cards</div>
    </div>
    <a href="{{ route('employees.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Employee</a>
</div>

@include('hr._tabs')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Name, code, email, phone" value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Position</label>
                <select name="position_id" class="form-select">
                    <option value="">All</option>
                    @foreach($positions as $p)
                        <option value="{{ $p->id }}" @selected((int) request('position_id') === $p->id)>{{ $p->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Campus</label>
                <select name="campus_id" class="form-select">
                    <option value="">All</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" @selected(\App\Support\CampusContext::filterFor(request()) === (int) $campus->id)>{{ $campus->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach($statuses as $k => $label)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('employees.index') }}" class="btn btn-light" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Employee</th><th>Code</th><th>Position</th><th>Campus</th>
                    <th>Contact</th><th class="text-center">Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($employee->photo)
                                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="" class="rounded-circle" width="20" height="20" style="object-fit:cover">
                                @else
                                    <span class="avatar-circle" style="width:32px;height:32px">{{ strtoupper(substr($employee->first_name, 0, 1)) }}</span>
                                @endif
                                <a href="{{ route('employees.show', $employee) }}" class="fw-semibold text-decoration-none">{{ $employee->fullName() }}</a>
                            </div>
                        </td>
                        <td><span class="badge text-bg-secondary">{{ $employee->employee_code }}</span></td>
                        <td>{{ $employee->position->title ?? '—' }}</td>
                        <td>{{ $employee->campus->name ?? '—' }}</td>
                        <td class="small">{{ $employee->phone ?: '—' }}<div class="text-muted">{{ $employee->email }}</div></td>
                        <td class="text-center">
                            @php $tb = ['active'=>'success','on_leave'=>'warning','resigned'=>'secondary','terminated'=>'danger'][$employee->status] ?? 'secondary'; @endphp
                            <span class="badge text-bg-{{ $tb }}">{{ $employee->statusLabel() }}</span>
                        </td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="ID Card" data-bs-toggle="modal" data-bs-target="#employeeCardModal" data-card-url="{{ route('employees.card', ['employee' => $employee, 'preview' => 1]) }}" data-employee-name="{{ $employee->fullName() }}"><i class="bi bi-person-vcard"></i></button>
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('employees.destroy', $employee) }}" class="d-inline"
                                  onsubmit="return confirm('You are about to delete the **employee** {{ $employee->fullName() }}. This action cannot be undone. Would you like to proceed?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state py-5"><i class="bi bi-person-vcard"></i><p class="mb-0">No employees yet. Add your first employee.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
        <div class="card-footer bg-transparent">{{ $employees->links() }}</div>
    @endif
</div>
@include('hr._card-preview-modal')
@endsection
