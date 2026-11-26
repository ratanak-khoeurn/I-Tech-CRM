@extends('layouts.app')

@section('title', 'Import Students')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-upload text-primary me-1"></i>Bulk Import Students from CSV</div>
            <div class="card-body">
                <form method="POST" action="{{ route('students.import') }}" enctype="multipart/form-data" data-loading>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">CSV File <span class="required-mark">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                        <div class="form-text">UTF-8 encoded CSV with a header row. Maximum 10MB.</div>
                        @error('file')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-upload me-1"></i>Import</button>
                        <a href="{{ route('students.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>

                <hr>
                <h6 class="form-section-title">Expected CSV Format</h6>
                <p class="small text-muted mb-2">Required columns: <code>English Name</code>, <code>Phone</code>. Optional columns are matched by header name (case-insensitive).</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered small mb-3">
                        <thead><tr>
                            <th>English Name *</th><th>Phone *</th><th>Khmer Name</th><th>Email</th><th>Gender</th>
                            <th>Telegram</th><th>Province</th><th>Course</th><th>Campus</th><th>Graduation Year</th>
                        </tr></thead>
                        <tbody>
                            <tr>
                                <td>Sok Dara</td><td>012345678</td><td>សុខ ដារ៉ា</td><td>dara@example.com</td><td>male</td>
                                <td>@dara</td><td>Phnom Penh</td><td>Web Development</td><td>Phnom Penh Main Campus</td><td>2023</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <a class="btn btn-outline-success btn-sm" href="{{ url('samples/students-import-template.csv') }}" download>
                    <i class="bi bi-download me-1"></i>Download CSV Template
                </a>
                <div class="alert alert-info small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Rows with duplicate phone or email (matching existing students) are automatically skipped and reported.
                    Student IDs are generated automatically. Imported students are assigned to you.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
