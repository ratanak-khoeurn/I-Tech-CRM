@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-folder2-open text-primary me-1"></i>Document Management</h1>
    <div class="page-subtitle">Photos, certificates, CVs, transcripts and supporting documents {{ auth()->user()->canViewSensitiveData() ? '' : '(sensitive documents are hidden for your role)' }}</div>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-0">Search</label>
                <input type="search" name="q" class="form-control form-control-sm" placeholder="Document name or student..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-0">Type</label>
                <select name="doc_type" class="form-select form-select-sm">
                    <option value="">All types</option>
                    @foreach(\App\Models\StudentDocument::TYPES as $k => $lbl)
                        <option value="{{ $k }}" @selected(request('doc_type') === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('documents.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Document</th><th>Student</th><th>Type</th><th>Size</th><th>Uploaded</th><th>By</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse($documents as $doc)
                    <tr>
                        <td class="small fw-semibold">
                            <i class="bi bi-file-earmark me-1 text-primary"></i>{{ $doc->name }}
                            @if($doc->is_sensitive)<span class="badge bg-danger ms-1" style="font-size:.6rem">SENSITIVE</span>@endif
                            @if($doc->description)<div class="text-muted fw-normal" style="font-size:.73rem">{{ $doc->description }}</div>@endif
                        </td>
                        <td><a href="{{ route('students.show', [$doc->student_id, 'tab' => 'documents']) }}" class="small text-decoration-none">{{ $doc->student?->name_english }}</a>
                            <div class="text-muted" style="font-size:.72rem">{{ $doc->student?->student_code }}</div></td>
                        <td class="small">{{ \App\Models\StudentDocument::TYPES[$doc->doc_type] ?? $doc->doc_type }}</td>
                        <td class="small">{{ $doc->file_size ? number_format($doc->file_size / 1024, 1) . ' KB' : '—' }}</td>
                        <td class="small">{{ $doc->created_at->format('M d, Y') }}</td>
                        <td class="small">{{ $doc->uploader?->name ?? '—' }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('documents.download', $doc) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                            @if(auth()->user()->canManageStudents())
                                <form action="{{ route('documents.destroy', $doc) }}" method="POST" class="d-inline" onsubmit="return confirm('You are about to permanently delete this **document**. This action cannot be undone. Would you like to proceed?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state"><i class="bi bi-folder2-open"></i>No documents found. Upload documents from a student's profile page.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap">
        <small class="text-muted">{{ $documents->total() }} document(s)</small>
        {{ $documents->links() }}
    </div>
</div>
@endsection
