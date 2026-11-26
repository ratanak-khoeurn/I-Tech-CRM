@extends('layouts.app')

@section('title', 'Study Groups')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-people-fill text-primary me-1"></i>Study Groups</h1>
        <div class="page-subtitle">Organize students into cohorts and mark them graduated together.</div>
    </div>
    <a href="{{ route('study-groups.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Create Study Group
    </a>
</div>

<div class="alert alert-info py-2">
    <i class="bi bi-info-circle me-1"></i>Create a group, add current students, then select eligible members to record graduation in one action.
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Study Group</th>
                    <th>Campus / Category</th>
                    <th>Academic Year / Intake</th>
                    <th>Teacher</th>
                    <th class="text-center">Students</th>
                    <th class="text-center">Graduated</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $group)
                    <tr>
                        <td>
                            <a href="{{ route('study-groups.show', $group) }}" class="fw-semibold text-decoration-none">{{ $group->name }}</a>
                            @if($group->code)<div class="text-muted small">{{ $group->code }}</div>@endif
                            @if($group->description)<div class="text-muted small">{{ \Illuminate\Support\Str::limit($group->description, 100) }}</div>@endif
                        </td>
                        <td>{{ $group->campus?->name ?? '—' }}<div class="text-muted small">{{ $group->courseCategory?->name_en ?? '—' }}</div></td>
                        <td>{{ $group->academicYear?->academic_year ?? '—' }}<div class="text-muted small">{{ $group->intake?->name ?? '—' }}</div></td>
                        <td>{{ $group->teacher?->fullName() ?? 'Not assigned' }}<div class="text-muted small">{{ $group->teacher?->position?->title }}</div></td>
                        <td class="text-center">{{ $group->students_count }}</td>
                        <td class="text-center"><span class="badge text-bg-success">{{ $group->graduated_students_count }}</span></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('study-groups.show', $group) }}" class="btn btn-sm btn-outline-secondary" title="Manage students"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('study-groups.edit', $group) }}" class="btn btn-sm btn-outline-primary" title="Edit group"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('study-groups.destroy', $group) }}" class="d-inline" onsubmit="return confirm('You are about to delete this **study group**. Student records will not be deleted. Would you like to proceed?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete group"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state py-5">
                                <i class="bi bi-people"></i>
                                <p class="mb-2">No study groups yet.</p>
                                <a href="{{ route('study-groups.create') }}" class="btn btn-sm btn-primary">Create the first group</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($groups->hasPages())
        <div class="card-footer bg-transparent">{{ $groups->links() }}</div>
    @endif
</div>
@endsection
