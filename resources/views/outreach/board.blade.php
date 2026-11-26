@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/kanban-board.css') }}" rel="stylesheet">
<style>
    @foreach ($colors as $stage => $color)
        .kb-col[data-stage="{{ $stage }}"] .kb-dot,
        .kb-col[data-stage="{{ $stage }}"] .kb-count { background: var(--bs-{{ $color }}); }
        .kb-card[data-stage="{{ $stage }}"] { border-left-color: var(--bs-{{ $color }}); }
    @endforeach
    .kb-card .kb-sub { font-size: .71rem; color: #6b7c93; }
    .kb-card .kb-actions .btn { font-size: .7rem; padding: .12rem .45rem; }
    .kb-add-list { max-height: 260px; overflow-y: auto; }
</style>
@endpush

@section('title', 'Outreach Board')

@php($audience = $campaign->audience_filters ?? [])

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="bi bi-kanban text-primary me-1"></i>{{ $campaign->name }}</h1>
        <div class="page-subtitle">
            <a href="{{ route('outreach.index') }}" class="text-decoration-none me-2"><i class="bi bi-arrow-left"></i> All campaigns</a>
            · {{ $campaign->purposeLabel() }}
            · {{ $totalLeads }} card(s)
            @if ($campaign->event)
                · linked to <a href="{{ route('events.show', $campaign->event) }}" class="text-decoration-none">{{ $campaign->event->title }}</a>
            @endif
            · <span class="badge rounded-pill bg-{{ $campaign->status === 'active' ? 'primary' : ($campaign->status === 'completed' ? 'success' : 'secondary') }}">{{ ucfirst($campaign->status) }}</span>
        </div>
        @if ($syncedRegistered > 0)
            <div class="alert alert-success py-1 px-2 mt-2 small mb-0">
                <i class="bi bi-check2-circle me-1"></i>{{ $syncedRegistered }} card(s) moved to Registered from the event registration list.
            </div>
        @endif
    </div>
    <div class="d-flex gap-2">
        @if ($canManage)
            <a href="{{ route('outreach.edit', $campaign) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        <a href="{{ route('students.index') }}" class="btn btn-light btn-sm border">Student list</a>
    </div>
</div>

@if ($canManage)
    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <div class="col-lg-7">
                    <div class="fw-semibold small mb-1">Audience filters</div>
                    @if ($audience === [])
                        <div class="text-muted small">
                            No filter saved yet — <a href="{{ route('outreach.edit', $campaign) }}">edit the campaign</a> and set
                            a course, campus, graduation year or interest flag first.
                        </div>
                    @else
                        <div class="kb-chips kb-chips-plain">
                            <span class="kb-chips-label">Audience</span>
                            @foreach ($audience as $key => $value)
                                @continue(is_array($value))
                                <span class="kb-chip is-plain">
                                    <i class="bi {{ ['course_id' => 'bi-book', 'campus_id' => 'bi-geo-alt', 'graduation_year' => 'bi-mortar-board', 'student_stage' => 'bi-person-badge', 'graduation_status' => 'bi-award', 'engagement_status' => 'bi-heart', 'alumni_group' => 'bi-people'][$key] ?? 'bi-funnel' }}"></i>
                                    {{ $flagLabels[$key] ?? ucwords(str_replace('_', ' ', $key)) }}<b>{{ $value }}</b>
                                </span>
                            @endforeach
                            @foreach ($audience['flags'] ?? [] as $flag => $on)
                                @continue(!$on)
                                <span class="kb-chip is-plain"><i class="bi bi-star"></i>{{ $flagLabels[$flag] }}</span>
                            @endforeach
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-people me-1"></i>{{ $audienceCount ?? 0 }} student(s) match these filters.
                        </div>
                    @endif
                </div>
                <div class="col-lg-5 text-lg-end">
                    @if ($audience !== [])
                        <form method="POST" action="{{ route('outreach.build-audience', $campaign) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-primary btn-sm rounded-pill px-3"
                                data-confirm="Add every student matching the audience filters that is not already on this board?">
                                <i class="bi bi-people me-1"></i>Add matching audience
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('outreach.board', $campaign) }}" class="kb-filterbar">
            <span class="kb-filter-label"><i class="bi bi-funnel"></i>Cards</span>
            <div class="kb-search">
                <i class="bi bi-search"></i>
                <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}"
                    placeholder="Find a card by name, code or phone">
                <button type="button" class="btn-close js-clear-filter" aria-label="Clear"></button>
            </div>
            <select name="campus_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="all" @selected($campusFilter === null)>All campuses</option>
                @foreach ($campuses as $campus)
                    <option value="{{ $campus->id }}" @selected($campusFilter === (int) $campus->id)>{{ $campus->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary btn-sm rounded-pill px-3"><i class="bi bi-funnel me-1"></i>Apply</button>
            @if (request('q') || $campusFilter !== null)
                <a href="{{ route('outreach.board', $campaign) }}" class="btn btn-light btn-sm border rounded-pill">Reset</a>
            @endif
            <span class="kb-chips-label ms-auto">{{ $totalLeads }} card(s) on this board</span>
        </form>

        @if ($canManage)
            <form method="GET" action="{{ route('outreach.board', $campaign) }}" class="kb-filterbar kb-filterbar-second">
                <span class="kb-filter-label"><i class="bi bi-person-plus"></i>Add</span>
                <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Pick a class…</option>
                    @foreach ($classes as $class)
                        @php($classLabel = trim($class->code . ' — ' . $class->name))
                        @if ($class->campus)
                            @php($classLabel .= ' · ' . $class->campus->name)
                        @endif
                        @if ($class->study_time)
                            @php($classLabel .= ' · ' . $class->study_time)
                        @endif
                        <option value="{{ $class->id }}" @selected($classFilter === (int) $class->id)>{{ $classLabel }}</option>
                    @endforeach
                </select>
                <div class="kb-search">
                    <i class="bi bi-search"></i>
                    <input type="search" name="add_q" class="form-control form-control-sm" value="{{ request('add_q') }}"
                        placeholder="…or search everyone by name, code or phone">
                    <button type="button" class="btn-close js-clear-filter" aria-label="Clear"></button>
                </div>
                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="campus_id" value="{{ $campusFilter === null ? 'all' : $campusFilter }}">
                <button class="btn btn-outline-primary btn-sm rounded-pill px-3"><i class="bi bi-list-check me-1"></i>Show people</button>
                @if ($classFilter !== null || request('add_q'))
                    <a href="{{ route('outreach.board', $campaign) }}" class="btn btn-light btn-sm border rounded-pill">Close</a>
                @endif
            </form>
        @endif
    </div>
</div>

@if ($canManage && $candidates)
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="POST" action="{{ route('outreach.leads.bulk', $campaign) }}" id="bulkAddForm">
                @csrf
                <input type="hidden" name="class_id" value="{{ $classFilter }}">
                <input type="hidden" name="add_q" value="{{ $candidates['term'] }}">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <div class="fw-semibold small">
                        <i class="bi bi-people text-primary me-1"></i>
                        @if ($candidates['class'])
                            {{ trim($candidates['class']->code . ' — ' . $candidates['class']->name) }}
                        @else
                            Search results @if ($candidates['term'] !== '')for &quot;{{ $candidates['term'] }}&quot;@endif
                        @endif
                        <span class="text-muted fw-normal">· {{ $candidates['total'] }} not on this board yet</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        @if ($candidates['students']->isNotEmpty())
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="pickAll">
                                <label class="form-check-label small" for="pickAll">Select all</label>
                            </div>
                        @endif
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3" id="pickSubmit" disabled>
                            <i class="bi bi-plus-lg me-1"></i>Add <span id="pickCount">0</span> selected
                        </button>
                    </div>
                </div>

                @if ($candidates['capped'])
                    <div class="alert alert-warning py-1 px-2 small mb-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>Showing the first {{ $candidates['students']->count() }}
                        of {{ $candidates['total'] }} — narrow the class or the search to reach the rest.
                    </div>
                @endif

                <div class="kb-add-list">
                    @forelse ($candidates['students'] as $person)
                        @php($blocked = $person->status === 'do_not_contact')
                        <div class="kb-add-row{{ $blocked ? ' kb-add-blocked' : '' }}">
                            <div class="d-flex align-items-start gap-2">
                                <input type="checkbox" class="form-check-input mt-1 flex-none"
                                    name="student_ids[]" value="{{ $person->id }}" id="pick-{{ $person->id }}"
                                    @if ($blocked) disabled @endif>
                                <label class="kb-pick-label small" for="pick-{{ $person->id }}">
                                    <span class="fw-semibold">{{ $person->name_english }}</span>
                                    <span class="text-muted">{{ $person->student_code }}</span>
                                    @if ($blocked)
                                        <span class="badge text-bg-danger">Do Not Contact</span>
                                    @endif
                                    <span class="kb-sub">{{ $person->phone ?: '—' }}
                                        @if ($person->telegram) · TG {{ $person->telegram }} @endif
                                        @if ($person->last_contacted_date) · last contact {{ $person->last_contacted_date->format('Y-m-d') }} @endif
                                    </span>
                                </label>
                                <a href="{{ route('students.show', $person) }}" class="btn btn-link btn-sm p-0 align-self-center"
                                    title="Open student"><i class="bi bi-box-arrow-up-right"></i></a>
                            </div>
                        </div>
                    @empty
                        <div class="kb-empty"><i class="bi bi-inbox"></i>Nobody left to add from this class or search.</div>
                    @endforelse
                </div>
            </form>
        </div>
    </div>
@endif

<div class="kb-wrap {{ $canManage ? 'can-manage' : '' }}">
    <div class="kb-board" id="outreachBoard" data-can-manage="{{ $canManage ? 1 : 0 }}"
        data-field="stage" data-empty-text="Nobody in this stage.">
        @foreach ($columns as $stage => $label)
            <div class="kb-col" data-stage="{{ $stage }}">
                <div class="kb-col-head">
                    <span class="kb-dot"></span>
                    <span>{{ $label }}</span>
                    <span class="kb-count" data-count-for="{{ $stage }}">{{ $counts[$stage] }}</span>
                </div>
                <div class="kb-body" data-stage="{{ $stage }}">
                    @forelse ($grouped[$stage] as $lead)
                        @php($person = $lead->student)
                        <div class="kb-card" data-card-id="{{ $lead->id }}" data-stage="{{ $stage }}"
                            @if ($canManage) draggable="true" @endif
                            data-url="{{ route('outreach.leads.stage', $lead) }}"
                            data-contact-url="{{ route('outreach.leads.contact', $lead) }}"
                            data-name="{{ $person->name_english }}">
                            <a class="kb-name" href="{{ route('students.show', $person) }}">{{ $person->name_english }}</a>
                            <div class="kb-meta">
                                {{ $person->student_code }}
                                @if ($person->status === 'do_not_contact')
                                    <span class="badge text-bg-danger">Do Not Contact</span>
                                @endif
                            </div>
                            <div class="kb-sub">
                                <i class="bi bi-telephone me-1"></i>{{ $person->phone ?: '—' }}
                                @if ($person->telegram)
                                    <span class="ms-2"><i class="bi bi-send me-1"></i>{{ $person->telegram }}</span>
                                @endif
                            </div>
                            @if ($lead->contacted_at)
                                <div class="kb-sub">
                                    <i class="bi bi-chat-dots me-1"></i>{{ $lead->contacted_at->format('Y-m-d H:i') }}
                                    @if ($lead->channel)
                                        · {{ \App\Models\CommunicationLog::CHANNELS[$lead->channel] ?? $lead->channel }}
                                    @endif
                                </div>
                            @endif
                            @if ($lead->note)
                                <div class="kb-sub text-truncate" title="{{ $lead->note }}">{{ $lead->note }}</div>
                            @endif
                            @if ($canManage)
                                <div class="kb-actions">
                                    <button type="button" class="btn btn-outline-primary rounded-pill"
                                        data-contact-card="{{ $lead->id }}"
                                        data-contact-url="{{ route('outreach.leads.contact', $lead) }}"
                                        data-contact-name="{{ $person->name_english }}">
                                        <i class="bi bi-pencil-square me-1"></i>Log contact
                                    </button>
                                    <form method="POST" action="{{ route('outreach.leads.destroy', $lead) }}"
                                        data-confirm="Remove {{ $person->name_english }} from this board?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger rounded-pill"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="kb-empty"><i class="bi bi-inbox"></i>Nobody in this stage.</div>
                    @endforelse
                    @if ($counts[$stage] > $columnLimit)
                        <div class="kb-limit">Showing first {{ $columnLimit }} of {{ $counts[$stage] }} — narrow the filters to see more.</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

@if ($canManage)
    <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="contactForm" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Record contact</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="small text-muted mb-3">
                            This writes to the student's communication history and moves their card. Nothing is sent
                            automatically.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Channel <span class="text-danger">*</span></label>
                            <select name="channel" class="form-select" required>
                                @foreach ($channels as $key => $label)
                                    <option value="{{ $key }}" @selected($key === 'phone_call')>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Result</label>
                            <select name="outcome" class="form-select">
                                @foreach ($columns as $stage => $label)
                                    <option value="{{ $stage }}" @selected($stage === 'contacted')>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">What was said?</label>
                            <textarea name="note" rows="3" class="form-control" maxlength="2000"
                                placeholder="Answered, asked to call back next week, said no thanks..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('outreach.board', $campaign) }}" class="btn btn-light" id="contactBoardLink">Stay on board</a>
                        <button class="btn btn-primary">Save contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script src="{{ asset('js/kanban-board.js') }}"></script>
@if ($canManage)
<script>
    (function () {
        const modalEl = document.getElementById('contactModal');
        const form = document.getElementById('contactForm');
        const title = modalEl.querySelector('.modal-title');
        const boardLink = document.getElementById('contactBoardLink');
        const modal = new bootstrap.Modal(modalEl);

        document.getElementById('outreachBoard').addEventListener('click', (event) => {
            const button = event.target.closest('[data-contact-card]');
            if (!button) return;
            form.action = button.dataset.contactUrl;
            title.textContent = 'Record contact — ' + button.dataset.contactName;
            boardLink.href = '{{ route('outreach.board', $campaign) }}';
            form.elements.note.value = '';
            modal.show();
        });
    })();

    (function () {
        const form = document.getElementById('bulkAddForm');
        if (!form) return;
        const boxes = Array.from(form.querySelectorAll('input[name="student_ids[]"]:not([disabled])'));
        const all = document.getElementById('pickAll');
        const count = document.getElementById('pickCount');
        const submit = document.getElementById('pickSubmit');

        const refresh = () => {
            const n = boxes.filter((b) => b.checked).length;
            count.textContent = n;
            submit.disabled = n === 0;
            if (all) {
                all.checked = n > 0 && n === boxes.length;
                all.indeterminate = n > 0 && n < boxes.length;
            }
        };

        boxes.forEach((box) => box.addEventListener('change', refresh));
        if (all) {
            all.addEventListener('change', () => boxes.forEach((box) => { box.checked = all.checked; }));
        }
        refresh();
    })();
</script>
@endif
<script>
    KanbanBoard.init('outreachBoard');
</script>
@endpush
