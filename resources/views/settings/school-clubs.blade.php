
@extends('layouts.app')

@section('title', 'School Clubs')

@section('content')

<style>
    /* =========================
       PAGE LAYOUT
    ========================= */
    .school-clubs-page {
        width: 100%;
        min-width: 0;
        padding: 1.25rem;
    }

    .school-clubs-page *,
    .school-clubs-page *::before,
    .school-clubs-page *::after {
        box-sizing: border-box;
    }

    .school-clubs-page .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }

    .school-clubs-page .page-title {
        margin: 0 0 0.35rem;
        font-size: 1.6rem;
        font-weight: 700;
        color: #212529;
    }

    .school-clubs-page .page-subtitle {
        margin: 0;
        color: #6c757d;
        font-size: 0.925rem;
    }

    /* =========================
       CARDS
    ========================= */
    .school-clubs-page .card {
        min-width: 0;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.035);
    }

    .school-clubs-page .card-header {
        padding: 1rem 1.25rem;
        background: #fff;
        border-bottom: 1px solid #edf0f2;
        border-radius: 12px 12px 0 0;
    }

    .school-clubs-page .card-body {
        padding: 1.25rem;
    }

    .school-clubs-page .section-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #212529;
    }

    /* =========================
       SUMMARY CARDS
    ========================= */
    .school-clubs-page .summary-card {
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .school-clubs-page .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 16px rgba(0, 0, 0, 0.07);
    }

    .school-clubs-page .summary-card .card-body {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.15rem;
    }

    .school-clubs-page .summary-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        font-size: 1.35rem;
    }

    .school-clubs-page .summary-label {
        margin-bottom: 0.25rem;
        color: #6c757d;
        font-size: 0.85rem;
    }

    .school-clubs-page .summary-value {
        margin: 0;
        color: #212529;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }

    /* =========================
       SEARCH AND FILTERS
    ========================= */
    .school-clubs-page .club-toolbar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        /* flex-wrap: wrap; */
    }

    .school-clubs-page .club-toolbar .search-control {
        flex: 1 1 250px;
        min-width: 0;
    }

    .school-clubs-page .club-toolbar .filter-control {
        flex: 0 1 180px;
        min-width: 150px;
    }

    .school-clubs-page .club-toolbar .form-control,
    .school-clubs-page .club-toolbar .form-select {
        min-height: 42px;
    }

    /* =========================
       FORM CONTROLS
    ========================= */
    .school-clubs-page .form-label,
    #createClubModal .form-label {
        display: block;
        margin-bottom: 0.4rem;
        color: #343a40;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .school-clubs-page .form-control,
    .school-clubs-page .form-select,
    #createClubModal .form-control,
    #createClubModal .form-select {
        border-radius: 8px;
        padding: 0.65rem 0.8rem;
    }

    .school-clubs-page .form-control:focus,
    .school-clubs-page .form-select:focus,
    #createClubModal .form-control:focus,
    #createClubModal .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.12);
    }

    #createClubModal textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    /* =========================
       TABLE AND SCROLLING
    ========================= */
    .school-clubs-page .table-responsive {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .school-clubs-page .table-responsive::-webkit-scrollbar {
        height: 7px;
    }

    .school-clubs-page .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .school-clubs-page .clubs-table {
        width: 100%;
        min-width: 1050px;
        margin-bottom: 0;
        vertical-align: middle;
    }

    .school-clubs-page .clubs-table thead th {
        padding: 0.9rem 1rem;
        background: #f8f9fa;
        color: #495057;
        border-bottom: 1px solid #e9ecef;
        font-size: 0.77rem;
        font-weight: 700;
        letter-spacing: 0.035em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .school-clubs-page .clubs-table tbody td {
        padding: 1rem;
        border-color: #f0f2f4;
    }

    .school-clubs-page .clubs-table tbody tr {
        transition: background-color 0.15s ease;
    }

    .school-clubs-page .clubs-table tbody tr:hover {
        background-color: #f8fbff;
    }

    .school-clubs-page .club-description {
        max-width: 260px;
        color: #6c757d;
        overflow-wrap: anywhere;
    }

    .school-clubs-page .club-image {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        object-fit: cover;
        border: 1px solid #e9ecef;
        border-radius: 10px;
    }

    .school-clubs-page .club-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        border-radius: 10px;
        background: #eef4ff;
        color: #0d6efd;
        font-size: 1.25rem;
    }

    .school-clubs-page .club-name {
        margin-bottom: 0.2rem;
        color: #212529;
        font-weight: 700;
    }

    .school-clubs-page .club-meta {
        color: #6c757d;
        font-size: 0.8rem;
    }

    .school-clubs-page .club-progress {
        height: 6px;
        min-width: 90px;
        margin-top: 0.5rem;
        border-radius: 10px;
        background: #e9ecef;
    }

    .school-clubs-page .club-progress .progress-bar {
        border-radius: 10px;
    }

    .school-clubs-page .action-buttons {
        display: flex;
        justify-content: center;
        gap: 0.4rem;
    }

    /* =========================
       EMPTY STATE
    ========================= */
    .school-clubs-page .empty-state {
        padding: 3.5rem 1rem;
        text-align: center;
    }

    .school-clubs-page .empty-state-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: #f0f4f8;
        color: #6c757d;
        font-size: 1.7rem;
    }

    /* =========================
       CREATE CLUB MODAL
    ========================= */
    #createClubModal .modal-dialog {
        width: calc(100% - 2rem);
        max-width: 850px;
        margin: 1rem auto;
    }

    #createClubModal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 14px;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
    }

    #createClubModal .modal-header {
        padding: 1.25rem 1.5rem;
        background: #fff;
        border-bottom: 1px solid #edf0f2;
    }

    #createClubModal .modal-title {
        font-size: 1.15rem;
        font-weight: 700;
    }

    #createClubModal .modal-body {
        max-height: calc(100vh - 220px);
        overflow-y: auto;
        padding: 1.5rem;
        overscroll-behavior: contain;
        scrollbar-width: thin;
    }

    #createClubModal .modal-footer {
        padding: 1rem 1.5rem;
        background: #fff;
        border-top: 1px solid #edf0f2;
    }

    #createClubModal .form-section-title {
        margin: 0 0 1rem;
        padding-bottom: 0.65rem;
        border-bottom: 1px solid #edf0f2;
        font-size: 0.95rem;
        font-weight: 700;
    }

    #createClubModal .form-section {
        margin-bottom: 1.5rem;
    }

    #createClubModal .form-section:last-child {
        margin-bottom: 0;
    }

    /* =========================
       MOBILE RESPONSIVENESS
    ========================= */
    @media (max-width: 767.98px) {
        .school-clubs-page {
            padding: 0.75rem;
        }

        .school-clubs-page .page-header {
            align-items: stretch;
            margin-bottom: 1rem;
        }

        .school-clubs-page .page-title {
            font-size: 1.35rem;
        }

        .school-clubs-page .page-header .btn {
            width: 100%;
        }

        .school-clubs-page .card-header,
        .school-clubs-page .card-body {
            padding: 0.9rem;
        }

        .school-clubs-page .summary-card .card-body {
            padding: 0.9rem;
            gap: 0.75rem;
        }

        .school-clubs-page .summary-icon {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
        }

        .school-clubs-page .summary-value {
            font-size: 1.25rem;
        }

        .school-clubs-page .club-toolbar {
            display: flex;
            align-items: stretch;
        }

        .school-clubs-page .club-toolbar .search-control,
        .school-clubs-page .club-toolbar .filter-control {
            width: 100%;
            min-width: 0;
            flex-basis: auto;
        }

        #createClubModal .modal-dialog {
            width: auto;
            margin: 0.5rem;
        }

        #createClubModal .modal-header,
        #createClubModal .modal-footer {
            padding: 1rem;
        }

        #createClubModal .modal-body {
            max-height: calc(100dvh - 160px);
            padding: 1rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .school-clubs-page .summary-card,
        .school-clubs-page .clubs-table tbody tr {
            transition: none;
        }
    }
</style>

<div class="school-clubs-page">

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">School Clubs</h1>
            <p class="page-subtitle">
                Manage school clubs, activities, fees and student enrollments.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-primary px-4 py-2"
            data-bs-toggle="modal"
            data-bs-target="#createClubModal"
        >
            <i class="bi bi-plus-lg me-1"></i>
            Create Club
        </button>
    </div>

    {{-- FLASH MESSAGES --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any() && !old('_form_context'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <strong>Please check the form.</strong>
            There are some validation errors.

            <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
        </div>
    @endif

    {{-- SUMMARY CARDS --}}
    @php
        $totalClubs = $clubs->count();
        $activeClubs = $clubs->where('is_active', true)->count();
        $totalEnrollments = $clubs->sum('students_count');
        $openRegistrations = $clubs->filter(
            fn ($club) => $club->is_registration_open
        )->count();
    @endphp

    <div class="row g-3 mb-4">

        <div class="col-6 col-xl-3">
            <div class="card summary-card">
                <div class="card-body">
                    <div class="summary-icon bg-primary-subtle text-primary">
                        <i class="bi bi-collection"></i>
                    </div>
                    <div>
                        <div class="summary-label">Total Clubs</div>
                        <p class="summary-value">{{ $totalClubs }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card summary-card">
                <div class="card-body">
                    <div class="summary-icon bg-success-subtle text-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="summary-label">Active Clubs</div>
                        <p class="summary-value">{{ $activeClubs }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card summary-card">
                <div class="card-body">
                    <div class="summary-icon bg-info-subtle text-info">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="summary-label">Enrollments</div>
                        <p class="summary-value">{{ $totalEnrollments }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card summary-card">
                <div class="card-body">
                    <div class="summary-icon bg-warning-subtle text-warning">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <div class="summary-label">Open Registration</div>
                        <p class="summary-value">{{ $openRegistrations }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- CLUB DIRECTORY --}}
    <div class="card mb-4">

        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="section-title">Club Directory</h2>
                    <small class="text-muted">
                        View and manage all school clubs.
                    </small>
                </div>

                <div class="club-toolbar">
                    <div class="search-control">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search text-muted"></i>
                            </span>

                            <input
                                type="search"
                                id="clubSearch"
                                class="form-control"
                                placeholder="Search clubs..."
                                aria-label="Search school clubs"
                            >
                        </div>
                    </div>

                    <div class="filter-control">
                        <select id="clubStatusFilter" class="form-select"
                            aria-label="Filter clubs by status">
                            <option value="all">All statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">

            @if ($clubs->isNotEmpty())

                <div class="table-responsive">
                    <table class="table clubs-table" id="clubsTable">
                        <thead>
                            <tr>
                                <th>Club Information</th>
                                <th>Category</th>
                                <th>Fee</th>
                                <th>Enrollment</th>
                                <th>Registration</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($clubs as $club)
                                @php
                                    $capacity = $club->capacity;
                                    $enrolled = (int) ($club->students_count ?? 0);

                                    $percentage = $capacity !== null && $capacity > 0
                                        ? min(100, round(($enrolled / $capacity) * 100))
                                        : 0;

                                    $isActive = (bool) $club->is_active;

                                    $searchText = strtolower(implode(' ', array_filter([
                                        $club->name,
                                        $club->code,
                                        $club->category,
                                        $club->description,
                                        $club->location,
                                        $club->coach_name,
                                    ])));
                                @endphp

                                <tr
                                    class="club-row"
                                    data-status="{{ $isActive ? 'active' : 'inactive' }}"
                                    data-search="{{ $searchText }}"
                                >
                                    {{-- CLUB INFORMATION --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-3">

                                            @if ($club->image)
                                                <img
                                                    src="{{ \Illuminate\Support\Facades\Storage::url($club->image) }}"
                                                    alt="{{ $club->name }}"
                                                    class="club-image"
                                                >
                                            @else
                                                <div class="club-placeholder">
                                                    <i class="bi bi-trophy"></i>
                                                </div>
                                            @endif

                                            <div class="min-w-0">
                                                <div class="club-name">
                                                    {{ $club->name }}
                                                </div>

                                                @if ($club->code)
                                                    <div class="club-meta">
                                                        Code: {{ $club->code }}
                                                    </div>
                                                @endif

                                                @if ($club->description)
                                                    <div class="club-description small mt-1">
                                                        {{ \Illuminate\Support\Str::limit($club->description, 85) }}
                                                    </div>
                                                @endif

                                                @if ($club->location)
                                                    <div class="club-meta mt-1">
                                                        <i class="bi bi-geo-alt me-1"></i>
                                                        {{ $club->location }}
                                                    </div>
                                                @endif
                                            </div>

                                        </div>
                                    </td>

                                    {{-- CATEGORY --}}
                                    <td>
                                        @if ($club->category)
                                            <span class="badge bg-light text-dark border">
                                                {{ $club->category }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- FEE --}}
                                    <td>
                                        <div class="fw-semibold">
                                            {{ number_format((float) $club->price, 2) }}
                                            {{ $club->currency ?: 'USD' }}
                                        </div>

                                        @if ($club->billing_period)
                                            <div class="club-meta">
                                                {{ ucwords(str_replace('_', ' ', $club->billing_period)) }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- ENROLLMENT --}}
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $enrolled }}
                                            @if ($capacity !== null)
                                                <span class="text-muted fw-normal">
                                                    / {{ $capacity }}
                                                </span>
                                            @endif
                                        </div>

                                        @if ($capacity !== null && $capacity > 0)
                                            <div
                                                class="progress club-progress"
                                                role="progressbar"
                                                aria-label="Club enrollment capacity"
                                                aria-valuenow="{{ $percentage }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100"
                                            >
                                                <div
                                                    class="progress-bar {{ $percentage >= 100 ? 'bg-danger' : ($percentage >= 80 ? 'bg-warning' : 'bg-primary') }}"
                                                    style="width: {{ $percentage }}%"
                                                ></div>
                                            </div>

                                            <small class="club-meta">
                                                {{ $percentage }}% full
                                            </small>
                                        @elseif ($capacity === null)
                                            <small class="club-meta">No capacity limit</small>
                                        @else
                                            <small class="text-muted">No capacity available</small>
                                        @endif
                                    </td>

                                    {{-- REGISTRATION --}}
                                    <td>
                                        @if ($club->is_registration_open)
                                            <span class="badge rounded-pill bg-success-subtle text-success">
                                                Open
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary">
                                                Closed
                                            </span>
                                        @endif

                                        @if ($club->registration_start || $club->registration_end)
                                            <div class="club-meta mt-2">
                                                @if ($club->registration_start)
                                                    {{ $club->registration_start->format('d M Y') }}
                                                @else
                                                    Anytime
                                                @endif

                                                <span class="mx-1">–</span>

                                                @if ($club->registration_end)
                                                    {{ $club->registration_end->format('d M Y') }}
                                                @else
                                                    No end date
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    {{-- STATUS --}}
                                    <td>
                                        @if ($isActive)
                                            <span class="badge rounded-pill bg-success-subtle text-success">
                                                Active
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>

                                    {{-- ACTIONS --}}
                                    <td>
                                        <div class="action-buttons">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-success"
                                                title="View club details"
                                                aria-label="View details for {{ $club->name }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewClubModal"
                                                data-image-url="{{ $club->image ? \Illuminate\Support\Facades\Storage::url($club->image) : '' }}"
                                                data-club="{{ json_encode($club->only(['id', 'name', 'code', 'description', 'category', 'price', 'currency', 'billing_period', 'capacity', 'location', 'schedule', 'start_date', 'end_date', 'registration_start', 'registration_end', 'coach_name', 'coach_email', 'coach_phone', 'is_active', 'students_count'])) }}"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Edit club"
                                                aria-label="Edit {{ $club->name }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#createClubModal"
                                                data-image-url="{{ $club->image ? \Illuminate\Support\Facades\Storage::url($club->image) : '' }}"
                                                data-club="{{ json_encode($club->only(['id', 'name', 'code', 'description', 'category', 'price', 'currency', 'billing_period', 'capacity', 'location', 'schedule', 'start_date', 'end_date', 'registration_start', 'registration_end', 'coach_name', 'coach_email', 'coach_phone', 'is_active'])) }}"
                                            >
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <form
                                                action="{{ route('school-clubs.destroy', $club) }}"
                                                method="POST"
                                                onsubmit="return confirm('You are about to delete this **club**. All student assignments for this club will also be removed. Would you like to proceed?');"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete club"
                                                    aria-label="Delete {{ $club->name }}"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- NO SEARCH RESULTS --}}
                            <tr id="noClubResults" style="display: none;">
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-search fs-2 text-muted d-block mb-2"></i>
                                    <div class="fw-semibold">No matching clubs found</div>
                                    <small class="text-muted">
                                        Try a different search term or status.
                                    </small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-3 border-top">
                    <small class="text-muted">
                        Showing <span id="visibleClubCount">{{ $clubs->count() }}</span>
                        of {{ $clubs->count() }} clubs
                    </small>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        id="resetClubFilters"
                    >
                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        Reset filters
                    </button>
                </div>

            @else

                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-collection"></i>
                    </div>

                    <h3 class="h5 fw-bold mb-2">No school clubs yet</h3>

                    <p class="text-muted mb-4">
                        Create your first club to start managing activities and enrollments.
                    </p>

                    <button
                        type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#createClubModal"
                    >
                        <i class="bi bi-plus-lg me-1"></i>
                        Create Your First Club
                    </button>
                </div>

            @endif
        </div>
    </div>

    <div class="text-muted small pb-2">
        <i class="bi bi-info-circle me-1"></i>
        Deleting a club also removes its student assignments. Make sure this action is intended.
    </div>

</div>

{{-- =========================
     CREATE CLUB MODAL
========================= --}}
<div
    class="modal fade"
    id="createClubModal"
    tabindex="-1"
    aria-labelledby="createClubModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <form
                action="{{ route('school-clubs.store') }}"
                method="POST"
                enctype="multipart/form-data"
                id="createClubForm"
            >
                @csrf
                <input type="hidden" name="_method" id="clubFormMethod" value="">
                <input type="hidden" name="_form_context" id="clubFormContext" value="{{ old('_form_context', 'create') }}">
                <input type="hidden" name="_club_id" id="clubFormId" value="{{ old('_club_id') }}">

                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="createClubModalLabel">
                            <i class="bi bi-plus-circle text-primary me-2"></i>
                            <span id="clubModalTitle">Create School Club</span>
                        </h2>

                        <p class="text-muted small mb-0 mt-1">
                            Enter the club details below.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body">

                    {{-- BASIC INFORMATION --}}
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            Basic Information
                        </h3>

                        <div class="row g-3">

                            <div class="col-md-8">
                                <label for="name" class="form-label">
                                    Club Name <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}"
                                    placeholder="e.g. Football Club"
                                    maxlength="255"
                                    required
                                >

                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="code" class="form-label">Club Code</label>

                                <input
                                    type="text"
                                    id="code"
                                    name="code"
                                    class="form-control @error('code') is-invalid @enderror"
                                    value="{{ old('code') }}"
                                    placeholder="e.g. CLUB-001"
                                    maxlength="50"
                                >

                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="category" class="form-label">Category</label>

                                <input
                                    type="text"
                                    id="category"
                                    name="category"
                                    class="form-control @error('category') is-invalid @enderror"
                                    value="{{ old('category') }}"
                                    placeholder="e.g. Sports, Arts, Technology"
                                    maxlength="100"
                                >

                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>

                                <input
                                    type="text"
                                    id="location"
                                    name="location"
                                    class="form-control @error('location') is-invalid @enderror"
                                    value="{{ old('location') }}"
                                    placeholder="e.g. Main Campus"
                                    maxlength="255"
                                >

                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>

                                <textarea
                                    id="description"
                                    name="description"
                                    class="form-control @error('description') is-invalid @enderror"
                                    rows="3"
                                    maxlength="2000"
                                    placeholder="Describe the purpose and activities of this club..."
                                >{{ old('description') }}</textarea>

                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="image" class="form-label">Club Image</label>

                                <input
                                    type="file"
                                    id="image"
                                    name="image"
                                    class="form-control @error('image') is-invalid @enderror"
                                    accept="image/png,image/jpeg,image/webp"
                                >

                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <small class="text-muted d-block mt-1">
                                    JPG, PNG or WebP; maximum 2 MB. Leave empty to keep the current image.
                                </small>

                                <div id="clubImagePreviewWrap" class="mt-3" hidden>
                                    <img
                                        id="clubImagePreview"
                                        src=""
                                        alt="Current club image"
                                        class="club-image"
                                    >
                                    <span class="text-muted small ms-2">Current image</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- FEES AND CAPACITY --}}
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="bi bi-cash-coin me-2 text-primary"></i>
                            Fees and Capacity
                        </h3>

                        <div class="row g-3">

                            <div class="col-md-4">
                                <label for="price" class="form-label">Fee</label>

                                <input
                                    type="number"
                                    id="price"
                                    name="price"
                                    class="form-control @error('price') is-invalid @enderror"
                                    value="{{ old('price', '0.00') }}"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                >

                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="currency" class="form-label">Currency</label>

                                <select
                                    id="currency"
                                    name="currency"
                                    class="form-select @error('currency') is-invalid @enderror"
                                >
                                    <option value="USD" @selected(old('currency', 'USD') === 'USD')>
                                        USD ($)
                                    </option>
                                    <option value="KHR" @selected(old('currency') === 'KHR')>
                                        KHR (៛)
                                    </option>
                                </select>

                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="billing_period" class="form-label">Billing Period</label>

                                <select
                                    id="billing_period"
                                    name="billing_period"
                                    class="form-select @error('billing_period') is-invalid @enderror"
                                >
                                    <option value="">Select period</option>
                                    <option value="one_time" @selected(old('billing_period') === 'one_time')>
                                        One Time
                                    </option>
                                    <option value="monthly" @selected(old('billing_period') === 'monthly')>
                                        Monthly
                                    </option>
                                    <option value="term" @selected(old('billing_period') === 'term')>
                                        Per Term
                                    </option>
                                    <option value="yearly" @selected(old('billing_period') === 'yearly')>
                                        Yearly
                                    </option>
                                </select>

                                @error('billing_period')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="capacity" class="form-label">Maximum Students</label>

                                <input
                                    type="number"
                                    id="capacity"
                                    name="capacity"
                                    class="form-control @error('capacity') is-invalid @enderror"
                                    value="{{ old('capacity') }}"
                                    min="1"
                                    step="1"
                                    placeholder="Leave blank for unlimited"
                                >

                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <small class="text-muted">
                                    Leave blank if there is no enrollment limit.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label for="schedule" class="form-label">Schedule</label>

                                <input
                                    type="text"
                                    id="schedule"
                                    name="schedule"
                                    class="form-control @error('schedule') is-invalid @enderror"
                                    value="{{ old('schedule') }}"
                                    placeholder="e.g. Every Friday, 3:00–5:00 PM"
                                >

                                @error('schedule')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- DATES --}}
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="bi bi-calendar-event me-2 text-primary"></i>
                            Club and Registration Dates
                        </h3>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Club Start Date</label>

                                <input
                                    type="date"
                                    id="start_date"
                                    name="start_date"
                                    class="form-control @error('start_date') is-invalid @enderror"
                                    value="{{ old('start_date') }}"
                                >

                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="end_date" class="form-label">Club End Date</label>

                                <input
                                    type="date"
                                    id="end_date"
                                    name="end_date"
                                    class="form-control @error('end_date') is-invalid @enderror"
                                    value="{{ old('end_date') }}"
                                >

                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="registration_start" class="form-label">
                                    Registration Opens
                                </label>

                                <input
                                    type="date"
                                    id="registration_start"
                                    name="registration_start"
                                    class="form-control @error('registration_start') is-invalid @enderror"
                                    value="{{ old('registration_start') }}"
                                >

                                @error('registration_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="registration_end" class="form-label">
                                    Registration Closes
                                </label>

                                <input
                                    type="date"
                                    id="registration_end"
                                    name="registration_end"
                                    class="form-control @error('registration_end') is-invalid @enderror"
                                    value="{{ old('registration_end') }}"
                                >

                                @error('registration_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- COACH INFORMATION --}}
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="bi bi-person-badge me-2 text-primary"></i>
                            Coach Information
                        </h3>

                        <div class="row g-3">

                            <div class="col-md-4">
                                <label for="coach_name" class="form-label">Coach Name</label>

                                <input
                                    type="text"
                                    id="coach_name"
                                    name="coach_name"
                                    class="form-control @error('coach_name') is-invalid @enderror"
                                    value="{{ old('coach_name') }}"
                                    maxlength="255"
                                    placeholder="Coach's full name"
                                >

                                @error('coach_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="coach_email" class="form-label">Coach Email</label>

                                <input
                                    type="email"
                                    id="coach_email"
                                    name="coach_email"
                                    class="form-control @error('coach_email') is-invalid @enderror"
                                    value="{{ old('coach_email') }}"
                                    maxlength="255"
                                    placeholder="coach@example.com"
                                >

                                @error('coach_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="coach_phone" class="form-label">Coach Phone</label>

                                <input
                                    type="text"
                                    id="coach_phone"
                                    name="coach_phone"
                                    class="form-control @error('coach_phone') is-invalid @enderror"
                                    value="{{ old('coach_phone') }}"
                                    maxlength="30"
                                    placeholder="Phone number"
                                >

                                @error('coach_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="bi bi-toggle-on me-2 text-primary"></i>
                            Club Status
                        </h3>

                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="is_active"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', '1') == '1')
                            >

                            <label class="form-check-label fw-semibold" for="is_active">
                                Active club
                            </label>

                            <div class="form-text">
                                Active clubs can be made available for student registration
                                when the registration dates and capacity permit.
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-light border px-4"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary px-4" id="saveClubButton">
                        <i class="bi bi-check-lg me-1"></i>
                        <span id="saveClubButtonText">Create Club</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- =========================
     VIEW CLUB DETAILS MODAL
========================= --}}
<div
    class="modal fade"
    id="viewClubModal"
    tabindex="-1"
    aria-labelledby="viewClubModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title fs-5 fw-bold" id="viewClubModalLabel">
                        <i class="bi bi-eye text-primary me-2"></i>Club Details
                    </h2>
                    <p class="text-muted small mb-0 mt-1">Full information for this school club.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <img id="detailClubImage" class="img-fluid w-100 rounded-3 border mb-3"
                            style="max-height: 240px; object-fit: cover;" alt="Club image" hidden>
                        <div id="detailClubImagePlaceholder" class="club-placeholder w-100 mb-3"
                            style="height: 180px;">
                            <i class="bi bi-trophy"></i>
                        </div>

                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="text-muted small">Club status</span>
                            <span id="detailClubStatus" class="badge rounded-pill"></span>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <h3 id="detailClubName" class="h4 fw-bold mb-1"></h3>
                        <p id="detailClubDescription" class="text-muted mb-3"></p>

                        <div class="row g-3">
                            <div class="col-sm-6"><div class="text-muted small">Club Code</div><div id="detailClubCode" class="fw-semibold"></div></div>
                            <div class="col-sm-6"><div class="text-muted small">Category</div><div id="detailClubCategory" class="fw-semibold"></div></div>
                            <div class="col-sm-6"><div class="text-muted small">Fee</div><div id="detailClubFee" class="fw-semibold"></div></div>
                            <div class="col-sm-6"><div class="text-muted small">Billing Period</div><div id="detailClubBilling" class="fw-semibold"></div></div>
                            <div class="col-sm-6"><div class="text-muted small">Enrollment</div><div id="detailClubEnrollment" class="fw-semibold"></div></div>
                            <div class="col-sm-6"><div class="text-muted small">Location</div><div id="detailClubLocation" class="fw-semibold"></div></div>
                            <div class="col-12"><div class="text-muted small">Schedule</div><div id="detailClubSchedule" class="fw-semibold" style="white-space: pre-line;"></div></div>
                            <div class="col-12"><div class="text-muted small">Club Dates</div><div id="detailClubDates" class="fw-semibold"></div></div>
                            <div class="col-12"><div class="text-muted small">Registration Dates</div><div id="detailClubRegistrationDates" class="fw-semibold"></div></div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h3 class="h6 fw-bold mb-3"><i class="bi bi-person-badge text-primary me-2"></i>Coach Information</h3>
                <div class="row g-3">
                    <div class="col-md-4"><div class="text-muted small">Coach Name</div><div id="detailClubCoachName" class="fw-semibold"></div></div>
                    <div class="col-md-4"><div class="text-muted small">Coach Email</div><div id="detailClubCoachEmail" class="fw-semibold text-break"></div></div>
                    <div class="col-md-4"><div class="text-muted small">Coach Phone</div><div id="detailClubCoachPhone" class="fw-semibold"></div></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const detailModal = document.getElementById('viewClubModal');

    function setDetailText(id, value, fallback = '—') {
        const element = document.getElementById(id);
        element.textContent = value === null || value === undefined || value === ''
            ? fallback
            : value;
    }

    function formatClubDate(value) {
        if (!value) {
            return 'Not set';
        }

        const date = new Date(`${String(value).slice(0, 10)}T00:00:00`);

        return Number.isNaN(date.getTime())
            ? String(value)
            : new Intl.DateTimeFormat(undefined, {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            }).format(date);
    }

    detailModal?.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        if (!button?.dataset.club) {
            return;
        }

        const club = JSON.parse(button.dataset.club);
        const image = document.getElementById('detailClubImage');
        const imagePlaceholder = document.getElementById('detailClubImagePlaceholder');
        const imageUrl = button.dataset.imageUrl || '';
        const status = document.getElementById('detailClubStatus');

        setDetailText('detailClubName', club.name);
        setDetailText('detailClubDescription', club.description, 'No description provided.');
        setDetailText('detailClubCode', club.code);
        setDetailText('detailClubCategory', club.category);
        setDetailText('detailClubFee', `${Number(club.price || 0).toFixed(2)} ${club.currency || 'USD'}`);
        setDetailText('detailClubBilling', club.billing_period
            ? club.billing_period.replaceAll('_', ' ').replace(/\b\w/g, character => character.toUpperCase())
            : 'Not specified');
        setDetailText('detailClubEnrollment', `${club.students_count || 0} / ${club.capacity ?? 'Unlimited'}`);
        setDetailText('detailClubLocation', club.location);
        setDetailText('detailClubSchedule', club.schedule);
        setDetailText('detailClubDates', `${formatClubDate(club.start_date)} – ${formatClubDate(club.end_date)}`);
        setDetailText('detailClubRegistrationDates', `${formatClubDate(club.registration_start)} – ${formatClubDate(club.registration_end)}`);
        setDetailText('detailClubCoachName', club.coach_name);
        setDetailText('detailClubCoachEmail', club.coach_email);
        setDetailText('detailClubCoachPhone', club.coach_phone);

        status.textContent = club.is_active ? 'Active' : 'Inactive';
        status.className = `badge rounded-pill ${club.is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}`;

        image.src = imageUrl;
        image.hidden = !imageUrl;
        imagePlaceholder.hidden = Boolean(imageUrl);
    });

    const clubModal = document.getElementById('createClubModal');
    const clubForm = document.getElementById('createClubForm');
    const clubFormMethod = document.getElementById('clubFormMethod');
    const clubFormContext = document.getElementById('clubFormContext');
    const clubFormId = document.getElementById('clubFormId');
    const modalTitle = document.getElementById('clubModalTitle');
    const saveButtonText = document.getElementById('saveClubButtonText');
    const saveButton = document.getElementById('saveClubButton');
    const imageInput = document.getElementById('image');
    const imagePreviewWrap = document.getElementById('clubImagePreviewWrap');
    const imagePreview = document.getElementById('clubImagePreview');
    const storeUrl = @json(route('school-clubs.store'));
    const updateUrlTemplate = @json(route('school-clubs.update', ['schoolClub' => '__CLUB_ID__']));
    const initialFormContext = @json(old('_form_context', 'create'));
    const initialClubId = @json(old('_club_id'));

    function setClubImagePreview(url) {
        imagePreview.src = url || '';
        imagePreviewWrap.hidden = !url;
    }

    function getClubImageUrl(clubId) {
        const button = Array.from(document.querySelectorAll('[data-club]'))
            .find(function (item) {
                return String(JSON.parse(item.dataset.club).id) === String(clubId);
            });

        return button?.dataset.imageUrl || '';
    }

    function setClubFormMode(club, populate = true, imageUrl = '') {
        const editing = Boolean(club);

        if (editing) {
            clubForm.action = updateUrlTemplate.replace('__CLUB_ID__', encodeURIComponent(club.id));
            clubFormMethod.value = 'PUT';
            clubFormContext.value = 'edit';
            clubFormId.value = club.id;
            modalTitle.textContent = 'Edit School Club';
            saveButtonText.textContent = 'Save Changes';
            saveButton.querySelector('i').className = 'bi bi-check-lg me-1';
            setClubImagePreview(imageUrl || getClubImageUrl(club.id));

            if (!populate) {
                return;
            }

            Object.entries(club).forEach(function ([key, value]) {
                const field = clubForm.querySelector(`[name="${key}"]:not([type="hidden"])`);

                if (!field) {
                    return;
                }

                if (field.type === 'file') {
                    return;
                }

                if (field.type === 'checkbox') {
                    field.checked = Boolean(Number(value));
                } else {
                    field.value = field.type === 'date'
                        ? String(value ?? '').slice(0, 10)
                        : value ?? '';
                }
            });

            return;
        }

        clubForm.action = storeUrl;
        clubFormMethod.value = '';
        clubFormContext.value = 'create';
        clubFormId.value = '';
        modalTitle.textContent = 'Create School Club';
        saveButtonText.textContent = 'Create Club';
        saveButton.querySelector('i').className = 'bi bi-check-lg me-1';
        setClubImagePreview('');
        if (imageInput) {
            imageInput.value = '';
        }
    }

    clubModal?.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const clubData = button?.dataset.club;

        if (clubData) {
            setClubFormMode(JSON.parse(clubData), true, button.dataset.imageUrl);
        } else if (!button && initialFormContext === 'edit' && initialClubId) {
            setClubFormMode({ id: initialClubId }, false);
        } else {
            if (clubFormContext.value !== 'create') {
                clubForm.reset();
            }

            setClubFormMode(null);
        }
    });

    if (initialFormContext === 'edit' && initialClubId) {
        const club = Array.from(document.querySelectorAll('[data-club]'))
            .map(function (button) { return JSON.parse(button.dataset.club); })
            .find(function (item) { return String(item.id) === String(initialClubId); });

        if (club) {
            setClubFormMode(club, false, getClubImageUrl(club.id));
        }
    }

    const searchInput = document.getElementById('clubSearch');
    const statusFilter = document.getElementById('clubStatusFilter');
    const resetButton = document.getElementById('resetClubFilters');
    const table = document.getElementById('clubsTable');
    const noResultsRow = document.getElementById('noClubResults');
    const visibleCount = document.getElementById('visibleClubCount');

    function filterClubs() {
        if (!table) {
            return;
        }

        const query = (searchInput?.value || '').trim().toLowerCase();
        const status = statusFilter?.value || 'all';
        const rows = table.querySelectorAll('tbody tr.club-row');

        let visible = 0;

        rows.forEach(function (row) {
            const text = (row.dataset.search || '').toLowerCase();
            const rowStatus = row.dataset.status || '';

            const matchesSearch = text.includes(query);
            const matchesStatus = status === 'all' || rowStatus === status;
            const show = matchesSearch && matchesStatus;

            row.style.display = show ? '' : 'none';

            if (show) {
                visible++;
            }
        });

        if (noResultsRow) {
            noResultsRow.style.display =
                rows.length > 0 && visible === 0 ? '' : 'none';
        }

        if (visibleCount) {
            visibleCount.textContent = visible;
        }
    }

    searchInput?.addEventListener('input', filterClubs);
    statusFilter?.addEventListener('change', filterClubs);

    resetButton?.addEventListener('click', function () {
        if (searchInput) {
            searchInput.value = '';
        }

        if (statusFilter) {
            statusFilter.value = 'all';
        }

        filterClubs();
    });

    // Reopen the modal when Laravel redirects back with validation errors.
    @if ($errors->any())
        if (clubModal && window.bootstrap?.Modal) {
            bootstrap.Modal.getOrCreateInstance(clubModal).show();
        }
    @endif

    // Prevent accidental double submission.
    clubForm?.addEventListener('submit', function () {
        if (!clubForm.checkValidity()) {
            return;
        }

        if (saveButton) {
            saveButton.disabled = true;
            const progressText = clubFormMethod.value === 'PUT' ? 'Saving...' : 'Creating...';
            saveButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + progressText;
        }
    });
});
</script>

@endsection
