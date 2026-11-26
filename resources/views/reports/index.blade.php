@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<div class="mb-3">
    <h1 class="page-title"><i class="bi bi-graph-up text-primary me-1"></i>Reports & Analytics</h1>
    <div class="page-subtitle">Select a report, apply filters, then view or export as Excel / CSV / PDF</div>
</div>

<div class="row g-3">
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">Available Reports</div>
            <div class="list-group list-group-flush" id="reportList">
                @foreach($reports as $key => $label)
                    <button type="button" class="list-group-item list-group-item-action small" data-report="{{ $key }}">
                        <i class="bi bi-file-earmark-bar-graph me-1 text-primary"></i>{{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-funnel text-primary me-1"></i>Filters</span>
            </div>
            <div class="card-body py-3">
                <div class="row g-2">
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Date From</label>
                        <input type="date" id="f_date_from" class="form-control form-control-sm" value="{{ now()->subDays(30)->toDateString() }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Date To</label>
                        <input type="date" id="f_date_to" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Course</label>
                        <select id="f_course_id" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($courses as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Campus</label>
                        <select id="f_campus_id" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($campuses as $c)
                                <option value="{{ $c->id }}" @selected(\App\Support\CampusContext::filterFor(request()) === (int) $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Grad Year</label>
                        <select id="f_graduation_year" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($gradYears as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Who <span class="text-muted">(attendance)</span></label>
                        <select id="f_person_type" class="form-select form-select-sm">
                            <option value="">Students + staff</option>
                            <option value="student">Students only</option>
                            <option value="employee">Staff only</option>
                        </select>
                    </div>
                </div>
                <div class="form-text mt-2">The Campus filter starts on your signed-in campus; pick All to widen it. Empty date fields default to the last 30 days.</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span id="reportTitle" class="fw-semibold"><i class="bi bi-table text-primary me-1"></i>Select a report from the left</span>
                <div class="btn-group btn-group-sm" id="exportBtns" style="display:none">
                    <button class="btn btn-outline-success" data-format="excel"><i class="bi bi-file-earmark-excel me-1"></i>Excel</button>
                    <button class="btn btn-outline-primary" data-format="csv"><i class="bi bi-filetype-csv me-1"></i>CSV</button>
                    <button class="btn btn-outline-danger" data-format="pdf"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="reportBody" class="p-4">
                    <div class="empty-state"><i class="bi bi-bar-chart-line"></i>Choose a report to preview its data here.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentReport = null;

function filterParams() {
    const p = new URLSearchParams();
    ['date_from', 'date_to', 'course_id', 'campus_id', 'graduation_year', 'person_type'].forEach(k => {
        const v = document.getElementById('f_' + k).value;
        if (v) p.set(k, v);
    });
    return p;
}

function loadReport(key) {
    currentReport = key;
    const body = document.getElementById('reportBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    document.getElementById('exportBtns').style.display = 'flex';

    fetch(`{{ url('reports/data') }}/${key}?` + filterParams().toString(), { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            document.getElementById('reportTitle').innerHTML = '<i class="bi bi-table text-primary me-1"></i>' + data.title;
            if (!data.rows.length) {
                body.innerHTML = '<div class="empty-state"><i class="bi bi-inbox"></i>No data for this report with current filters.</div>';
                return;
            }
            let html = '<div class="table-responsive"><table class="table table-hover table-sm mb-0"><thead><tr>';
            data.columns.forEach(c => html += `<th>${c}</th>`);
            html += '</tr></thead><tbody>';
            data.rows.forEach(r => {
                html += '<tr>' + r.map(cell => `<td>${cell === null ? '—' : String(cell).replace(/</g, '&lt;')}</td>`).join('') + '</tr>';
            });
            html += '</tbody></table></div>';
            html += `<div class="p-2 small text-muted border-top">${data.rows.length} row(s)</div>`;
            body.innerHTML = html;
        })
        .catch(() => {
            body.innerHTML = '<div class="alert alert-danger m-3">Failed to load report data.</div>';
        });
}

document.querySelectorAll('#reportList button').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('#reportList button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        loadReport(btn.dataset.report);
    });
});

document.querySelectorAll('#exportBtns button').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!currentReport) return;
        window.location = `{{ url('reports/export') }}/${currentReport}/${btn.dataset.format}?` + filterParams().toString();
    });
});

// Auto-load the first report
document.querySelector('#reportList button')?.click();
</script>
@endpush
