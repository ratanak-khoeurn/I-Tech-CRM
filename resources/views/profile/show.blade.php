@extends('layouts.app')

@section('title', 'My Profile')

@php
    $capabilities = [
        'Manage students and alumni records' => $user->canManageStudents(),
        'View confidential student details' => $user->canViewSensitiveData(),
        'Access Finance, Academic and Settings' => $user->isAdmin(),
        'Manage user accounts and roles' => $user->isSuperAdmin(),
        'Export database backups' => $user->isSuperAdmin(),
    ];
    $initials = strtoupper(substr($user->name, 0, 1));
@endphp

@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">My Profile</li>
    </ol>
</nav>

<div class="card profile-header-card mb-3">
    <div class="card-body d-flex flex-wrap gap-3 align-items-center">
        @if($user->avatar)
            <img src="{{ asset('storage/' . $user->avatar) }}" class="profile-avatar" alt="{{ $user->name }}">
        @else
            <span class="profile-avatar-placeholder">{{ $initials }}</span>
        @endif
        <div class="flex-grow-1">
            <h4 class="mb-1">{{ $user->name }}
                <span class="badge bg-primary align-middle ms-1">{{ $user->role?->display_name ?? 'No role assigned' }}</span>
                <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }} align-middle">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
            </h4>
            <div class="small opacity-75">
                @if($user->email)<span class="me-3"><i class="bi bi-envelope me-1"></i>{{ $user->email }}</span>@endif
                @if($user->phone)<span class="me-3"><i class="bi bi-telephone me-1"></i>{{ $user->phone }}</span>@endif
                <span><i class="bi bi-calendar3 me-1"></i>Member since {{ $user->created_at?->format('M Y') }}</span>
            </div>
        </div>
        <div class="d-flex flex-column gap-2 text-end">
            <a href="{{ route('notifications.index') }}" class="btn btn-light btn-sm">
                <i class="bi bi-bell me-1"></i>Notifications
                @if($stats['unread_notifications'])
                    <span class="badge bg-danger ms-1">{{ $stats['unread_notifications'] }}</span>
                @endif
            </a>
            <a href="{{ route('follow-ups.index') }}" class="btn btn-light btn-sm"><i class="bi bi-check2-square me-1"></i>My follow-up tasks</a>
            @if($user->isSuperAdmin())
                <a href="{{ route('users.index') }}" class="btn btn-light btn-sm"><i class="bi bi-person-gear me-1"></i>Manage accounts</a>
            @endif
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach([
        ['assigned_students', 'Students assigned to me', 'people', ''],
        ['open_tasks', 'Open follow-up tasks', 'check2-square', 'teal'],
        ['overdue_tasks', 'Overdue tasks', 'exclamation-triangle', 'red'],
        ['communications', 'Communications logged', 'chat-dots', 'purple'],
    ] as [$key, $label, $icon, $color])
        <div class="col-6 col-lg-3">
            <div class="card stat-card {{ $color }} h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-value">{{ number_format($stats[$key]) }}</div>
                        <div class="stat-label">{{ $label }}</div>
                    </div>
                    <i class="bi bi-{{ $icon }} stat-icon"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">Account details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12"><div class="info-label">Full name</div><div class="info-value">{{ $user->name }}</div></div>
                    <div class="col-md-6"><div class="info-label">Email</div><div class="info-value">{{ $user->email ?: '—' }}</div></div>
                    <div class="col-md-6"><div class="info-label">Phone</div><div class="info-value">{{ $user->phone ?: '—' }}</div></div>
                    <div class="col-md-6">
                        <div class="info-label">Role</div>
                        <div class="info-value">{{ $user->role?->display_name ?? '—' }}</div>
                        @if($user->role)
                            <div class="small text-muted mt-1">{{ $user->role->description ?: 'No description set.' }}</div>
                        @endif
                    </div>
                    <div class="col-md-6"><div class="info-label">Status</div><div class="info-value">{{ $user->is_active ? 'Active' : 'Inactive' }}</div></div>
                    <div class="col-md-6"><div class="info-label">Created</div><div class="info-value">{{ $user->created_at?->format('M d, Y') ?? '—' }}</div></div>
                    <div class="col-md-6"><div class="info-label">Last updated</div><div class="info-value">{{ $user->updated_at?->format('M d, Y') ?? '—' }}</div></div>
                </div>
                <p class="text-muted small mb-0 mt-3">
                    <i class="bi bi-info-circle me-1"></i>To change these details, ask an administrator to edit your account under Administration → Users.
                </p>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header">What my role can do</div>
            <div class="card-body">
                @foreach($capabilities as $label => $allowed)
                    <div class="d-flex justify-content-between align-items-center small mb-2">
                        <span>{{ $label }}</span>
                        <span class="{{ $allowed ? 'text-success' : 'text-muted' }}">
                            @if($allowed)
                                <i class="bi bi-check-circle-fill me-1"></i>Allowed
                            @else
                                <i class="bi bi-dash-circle me-1"></i>Not allowed
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">My activity</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                        <tr><td class="text-muted">Invoices created</td><td class="text-end fw-semibold">{{ number_format($stats['invoices']) }}</td></tr>
                        <tr><td class="text-muted">Payments received</td><td class="text-end fw-semibold">{{ number_format($stats['payments']) }}</td></tr>
                        <tr><td class="text-muted">Documents uploaded</td><td class="text-end fw-semibold">{{ number_format($stats['documents']) }}</td></tr>
                        <tr><td class="text-muted">Unread notifications</td><td class="text-end fw-semibold">{{ number_format($stats['unread_notifications']) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
