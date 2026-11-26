@extends('layouts.app')

@section('title', 'Programs')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-mortarboard text-primary me-1"></i>Programs</h1>
        <div class="page-subtitle">Manage academic programs, then assign courses to them.</div>
    </div>
    <a href="{{ route('programs.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Program</a>
</div>

@include('academic._tabs')

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Program</th><th>Code</th><th>Description</th><th class="text-center">Courses</th><th class="text-center">Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                    <tr>
                        <td class="fw-semibold">{{ $program->name }}</td>
                        <td><span class="badge text-bg-secondary">{{ $program->code }}</span></td>
                        <td class="small text-muted">{{ \Illuminate\Support\Str::limit($program->description, 90) ?: '—' }}</td>
                        <td class="text-center">{{ $program->courses_count }}</td>
                        <td class="text-center">
                            <span class="badge text-bg-{{ $program->is_active ? 'success' : 'secondary' }}">{{ $program->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('programs.edit', $program) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('programs.destroy', $program) }}" class="d-inline" onsubmit="return confirm('You are about to delete the **program** {{ $program->name }}. This action cannot be undone. Would you like to proceed?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state py-5"><i class="bi bi-mortarboard"></i><p class="mb-0">No programs yet. Add a program before assigning courses.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($programs->hasPages())<div class="card-footer bg-transparent">{{ $programs->links() }}</div>@endif
</div>
@endsection
