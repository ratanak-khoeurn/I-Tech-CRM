@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-shield-check text-primary me-1"></i>Audit Logs</h1>
    <div class="page-subtitle">Track important changes made in the system</div>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-0">Search</label>
                <input type="search" name="q" class="form-control form-control-sm" placeholder="Description, action, entity..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-0">Action</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">All actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-0">User</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-funnel"></i></button>
                <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-light"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:160px;">When</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Description</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-nowrap text-muted small">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $log->user->name ?? 'System' }}</td>
                        <td>
                            @php($color = ['created' => 'success', 'updated' => 'primary', 'deleted' => 'danger', 'login' => 'info', 'logout' => 'secondary', 'merged' => 'warning'][$log->action] ?? 'secondary')
                            <span class="badge text-bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                        </td>
                        <td class="small">{{ $log->entity_type ? $log->entity_type . ($log->entity_id ? ' #' . $log->entity_id : '') : '—' }}</td>
                        <td class="small">{{ $log->description }}</td>
                        <td class="small text-muted">{{ $log->ip_address ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">
                        <div class="empty-state py-5"><i class="bi bi-shield-check"></i><p class="mb-0">No audit log entries match your filters.</p></div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-footer bg-transparent">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
