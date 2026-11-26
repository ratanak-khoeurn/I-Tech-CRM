@extends('layouts.app')

@section('title', 'Staff Accounts')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-person-badge text-primary me-1"></i>Staff Accounts</h1>
        <div class="page-subtitle">Manage system users and their roles</div>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Add Staff Account</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Campuses</th>
                    <th class="text-center">Assigned Students</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td class="fw-semibold">
                            <div class="d-flex align-items-center gap-2">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                                @else
                                    <span class="avatar-circle d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                @endif
                                <span>
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())<span class="badge text-bg-info ms-1">You</span>@endif
                                </span>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?: '—' }}</td>
                        <td>
                            @php($roleColor = ['super_admin' => 'danger', 'admin' => 'primary', 'staff' => 'info', 'viewer' => 'secondary'][$user->role->name ?? ''] ?? 'secondary')
                            <span class="badge text-bg-{{ $roleColor }}">{{ $user->role->display_name ?? '—' }}</span>
                        </td>
                        <td>
                            @if($user->campuses->isEmpty())
                                <span class="text-muted small">{{ $user->role?->name === 'staff' ? 'All campuses' : '—' }}</span>
                            @else
                                @foreach($user->campuses as $campus)
                                    <span class="badge text-bg-light border text-dark me-1">{{ $campus->name }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td class="text-center">{{ $user->assigned_students_count }}</td>                        <td class="text-center">
                            @if($user->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Disabled</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline"
                                      onsubmit="return confirm('You are about to delete the **account** {{ $user->email }}. Any students assigned to this user will be unassigned. Would you like to proceed?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-transparent">{{ $users->links() }}</div>
    @endif
</div>
@endsection
