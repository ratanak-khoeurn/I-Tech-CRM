@extends('layouts.app')

@section('title', 'Roles')

@section('content')

    <style>
        /* ================================
                   Roles Page
                ================================= */

        .roles-page {
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Page Header */
        .roles-header {
            background: linear-gradient(135deg, #1B3A6B 0%, #274d88 100%);
            border-radius: 16px;
            padding: 24px 28px;
            color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(27, 58, 107, 0.12);
        }

        .roles-header h4 {
            font-size: 1.35rem;
            font-weight: 600;
        }

        .roles-header p {
            color: rgba(255, 255, 255, 0.75);
        }

        .roles-header-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        .btn-add-role {
            background: #fff;
            color: #1B3A6B;
            border: none;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 9px;
            transition: all 0.2s ease;
        }

        .btn-add-role:hover {
            background: #f8f9fa;
            color: #1B3A6B;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
        }

        /* Summary */
        .summary-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 14px;
            padding: 18px;
            height: 100%;
            transition: all 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        .summary-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .summary-icon.blue {
            background: #e8f0ff;
            color: #1B3A6B;
        }

        .summary-icon.green {
            background: #e9f8ef;
            color: #198754;
        }

        .summary-icon.orange {
            background: #fff3df;
            color: #d97706;
        }

        .summary-number {
            font-size: 1.35rem;
            font-weight: 700;
            color: #212529;
            line-height: 1.2;
        }

        .summary-label {
            font-size: 0.78rem;
            color: #6c757d;
        }

        /* Main Card */
        .roles-card {
            border: 1px solid #e9ecef;
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.04);
        }

        .roles-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #edf0f2;
            background: #fff;
        }

        .roles-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
        }

        .roles-card-subtitle {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .role-count-badge {
            background: #e8f0ff;
            color: #1B3A6B;
            border-radius: 20px;
            padding: 7px 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Table */
        .roles-table {
            margin-bottom: 0;
        }

        .roles-table thead th {
            background: #f8f9fa;
            color: #6c757d;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 14px 16px;
            border-bottom: 1px solid #e9ecef;
            white-space: nowrap;
        }

        .roles-table tbody td {
            padding: 16px;
            border-bottom: 1px solid #f0f1f3;
            vertical-align: middle;
        }

        .roles-table tbody tr {
            transition: background-color 0.15s ease;
        }

        .roles-table tbody tr:hover {
            background: #f8fbff;
        }

        .roles-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Role Badge */
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f3f5;
            color: #343a40;
            border: 1px solid #e1e5e8;
            border-radius: 7px;
            padding: 6px 9px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .role-badge::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #1B3A6B;
        }

        .display-name {
            font-weight: 600;
            color: #212529;
        }

        .description-text {
            color: #6c757d;
            font-size: 0.84rem;
            max-width: 280px;
        }

        .user-count {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eef6ff;
            color: #1B3A6B;
            border-radius: 20px;
            padding: 5px 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .created-date {
            font-size: 0.78rem;
            color: #6c757d;
        }

        /* Actions */
        .role-actions {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .role-action-btn {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px !important;
            border: 1px solid #dee2e6;
            background: #fff;
            transition: all 0.18s ease;
            padding: 0;
        }

        .role-action-btn i {
            font-size: 0.9rem;
        }

        .role-action-view {
            color: #1B3A6B;
        }

        .role-action-view:hover {
            background: #eaf1ff;
            border-color: #b9cdf5;
            color: #1B3A6B;
            transform: translateY(-1px);
        }

        .role-action-edit {
            color: #6f42c1;
        }

        .role-action-edit:hover {
            background: #f2eafe;
            border-color: #d8c4f5;
            color: #6f42c1;
            transform: translateY(-1px);
        }

        .role-action-delete {
            color: #dc3545;
        }

        .role-action-delete:hover {
            background: #fff0f1;
            border-color: #f1b8be;
            color: #dc3545;
            transform: translateY(-1px);
        }

        /* Tooltip-like action text */
        .action-label {
            display: none;
        }

        /* Empty State */
        .empty-state {
            padding: 70px 20px;
            text-align: center;
        }

        .empty-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 18px;
            border-radius: 18px;
            background: #f1f4f8;
            color: #8b96a5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .empty-state h5 {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6c757d;
            margin-bottom: 22px;
        }

        /* Pagination */
        .roles-pagination {
            padding: 16px 24px;
            border-top: 1px solid #edf0f2;
            background: #fff;
        }

        .roles-pagination .pagination {
            margin-bottom: 0;
            justify-content: flex-end;
        }

        /* Responsive */
        @media (max-width: 768px) {

            .roles-header {
                padding: 20px;
            }

            .roles-header .header-content {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .roles-header .btn-add-role {
                width: 100%;
            }

            .summary-card {
                margin-bottom: 12px;
            }

            .roles-card-header {
                padding: 16px;
            }

            .roles-table tbody td {
                padding: 12px;
            }

            .roles-table thead th {
                padding: 12px;
            }
        }

        .btn-action-delete {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #dc3545;
            background: #fff;
            border: 1px solid #f1c2c7;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-action-delete:hover {
            color: #fff;
            background: #dc3545;
            border-color: #dc3545;
            transform: translateY(-1px);
        }

        .delete-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #fff1f2;
            color: #dc3545;
            font-size: 30px;
        }

        .modal-content {
            border-radius: 16px;
        }

        .modal-footer .btn {
            border-radius: 8px;
            min-width: 120px;
        }

        .modal-footer .btn-danger {
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.2);
        }
    </style>


    <div class="container-fluid roles-page">

        {{-- ================================
             Page Header
        ================================= --}}
        <div class="roles-header">

            <div class="d-flex justify-content-between align-items-center gap-3 header-content">

                <div class="d-flex align-items-center gap-3">

                    <div class="roles-header-icon">
                        <i class="bi bi-person-rolodex"></i>
                    </div>

                    <div>
                        <h4 class="mb-1">
                            Roles & Permissions
                        </h4>

                        <p class="mb-0">
                            Manage system roles and access levels.
                        </p>
                    </div>

                </div>

                <a href="{{ route('roles.create') }}" class="btn btn-add-role">
                    <i class="bi bi-plus-lg me-1"></i>
                    Add Role
                </a>

            </div>

        </div>


        {{-- ================================
             Summary Cards
        ================================= --}}
        <div class="row g-3 mb-4">

            {{-- Total Roles --}}
            <div class="col-xl-4 col-md-6">

                <div class="summary-card">

                    <div class="d-flex align-items-center gap-3">

                        <div class="summary-icon blue">
                            <i class="bi bi-person-rolodex"></i>
                        </div>

                        <div>

                            <div class="summary-number">
                                {{ $roles->total() }}
                            </div>

                            <div class="summary-label">
                                Total Roles
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Total Assigned Users --}}
            <div class="col-xl-4 col-md-6">

                <div class="summary-card">

                    <div class="d-flex align-items-center gap-3">

                        <div class="summary-icon green">
                            <i class="bi bi-people"></i>
                        </div>

                        <div>

                            <div class="summary-number">
                                {{ $roles->sum('users_count') }}
                            </div>

                            <div class="summary-label">
                                Assigned Users
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Current Page --}}
            <div class="col-xl-4 col-md-6">

                <div class="summary-card">

                    <div class="d-flex align-items-center gap-3">

                        <div class="summary-icon orange">
                            <i class="bi bi-list-ul"></i>
                        </div>

                        <div>

                            <div class="summary-number">
                                {{ $roles->count() }}
                            </div>

                            <div class="summary-label">
                                Roles on This Page
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ================================
             Roles Table
        ================================= --}}
        <div class="roles-card">

            {{-- Card Header --}}
            <div class="roles-card-header">

                <div class="d-flex justify-content-between align-items-center gap-3">

                    <div>

                        <div class="roles-card-title">
                            All System Roles
                        </div>

                        <div class="roles-card-subtitle">
                            View and manage roles assigned to staff accounts.
                        </div>

                    </div>

                    <span class="role-count-badge">
                        {{ $roles->total() }} Roles
                    </span>

                </div>

            </div>


            {{-- Table Content --}}
            @if ($roles->count())

                <div class="table-responsive">

                    <table class="table roles-table align-middle">

                        <thead>

                            <tr>

                                <th class="ps-4" style="width: 70px;">
                                    #
                                </th>

                                <th>
                                    Role
                                </th>

                                <th>
                                    Display Name
                                </th>

                                <th>
                                    Description
                                </th>

                                <th class="text-center">
                                    Users
                                </th>

                                <th>
                                    Created
                                </th>

                                <th class="text-end pe-4">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($roles as $role)
                                <tr>

                                    {{-- Number --}}
                                    <td class="ps-4 text-muted">
                                        {{ $roles->firstItem() + $loop->index }}
                                    </td>


                                    {{-- Role --}}
                                    <td>

                                        <span class="role-badge">
                                            {{ $role->name }}
                                        </span>

                                    </td>


                                    {{-- Display Name --}}
                                    <td>

                                        <span class="display-name">
                                            {{ $role->display_name }}
                                        </span>

                                    </td>


                                    {{-- Description --}}
                                    <td>

                                        @if ($role->description)
                                            <span class="description-text">
                                                {{ $role->description }}
                                            </span>
                                        @else
                                            <span class="text-muted fst-italic small">
                                                No description
                                            </span>
                                        @endif

                                    </td>


                                    {{-- Users --}}
                                    <td class="text-center">

                                        <span class="user-count">
                                            <i class="bi bi-people"></i>
                                            {{ $role->users_count }}
                                        </span>

                                    </td>


                                    {{-- Created --}}
                                    <td>

                                        <span class="created-date">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ $role->created_at?->format('d M Y') }}
                                        </span>

                                    </td>


                                    {{-- Actions --}}
                                    <td class="text-end pe-4">

                                        <div class="role-actions">

                                            {{-- View --}}
                                            <a href="{{ route('roles.show', $role) }}"
                                                class="role-action-btn role-action-view" title="View Role">

                                                <i class="bi bi-eye"></i>

                                            </a>


                                            {{-- Edit --}}
                                            <a href="{{ route('roles.edit', $role) }}"
                                                class="role-action-btn role-action-edit" title="Edit Role">

                                                <i class="bi bi-pencil"></i>

                                            </a>


                                            {{-- Delete --}}
                                            <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('You are about to delete this **role**. This action cannot be undone. Would you like to proceed?');">

                                                @csrf
                                                @method('DELETE')

                                                <button type="button" class="btn btn-sm btn-action-delete"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteRoleModal{{ $role->id }}"
                                                    title="Delete Role">
                                                    <i class="bi bi-trash3"></i>
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>
                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteRoleModal{{ $role->id }}" tabindex="-1"
                                    aria-labelledby="deleteRoleModalLabel{{ $role->id }}" aria-hidden="true">

                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg">

                                            <!-- Header -->
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title fw-bold"
                                                    id="deleteRoleModalLabel{{ $role->id }}">
                                                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                                    Delete Role
                                                </h5>

                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>

                                            <!-- Body -->
                                            <div class="modal-body text-center px-4 pb-4">

                                                <div class="delete-icon mb-3">
                                                    <i class="bi bi-trash3"></i>
                                                </div>

                                                <h5 class="fw-bold mb-2">
                                                    Are you sure?
                                                </h5>

                                                <p class="text-muted mb-3">
                                                    You are about to delete the role
                                                    <strong class="text-dark">
                                                        "{{ $role->display_name }}"
                                                    </strong>.
                                                </p>

                                                <div class="alert alert-warning border-0 text-start">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    This action cannot be undone.
                                                </div>

                                            </div>

                                            <!-- Footer -->
                                            <div class="modal-footer border-0 justify-content-center pb-4">

                                                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                                                    <i class="bi bi-x-lg me-1"></i>
                                                    Cancel
                                                </button>

                                                <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                                    class="d-inline">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-danger px-4">
                                                        <i class="bi bi-trash3 me-1"></i>
                                                        Yes, Delete
                                                    </button>

                                                </form>

                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </tbody>

                    </table>

                </div>

                {{-- Pagination --}}
                @if ($roles->hasPages())
                    <div class="roles-pagination">

                        {{ $roles->links() }}

                    </div>
                @endif
            @else
                {{-- Empty State --}}
                <div class="empty-state">

                    <div class="empty-icon">
                        <i class="bi bi-person-rolodex"></i>
                    </div>

                    <h5>
                        No Roles Found
                    </h5>

                    <p>
                        There are no roles in the system yet.
                    </p>

                    <a href="{{ route('roles.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>
                        Create Your First Role
                    </a>

                </div>

            @endif

        </div>

    </div>

@endsection
