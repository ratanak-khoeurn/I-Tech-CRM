@extends('layouts.app')

@section('title', 'Role Details')

@section('content')

    <style>
        /* ================================
           Role Details Page
        ================================= */

        .role-details-page {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .role-details-header {
            background: linear-gradient(135deg, #1B3A6B 0%, #274d88 100%);
            border-radius: 16px;
            padding: 24px 28px;
            color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(27, 58, 107, 0.12);
        }

        .role-details-header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .header-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .role-details-header h4 {
            font-size: 1.35rem;
            font-weight: 600;
        }

        .role-details-header p {
            color: rgba(255, 255, 255, 0.75);
        }

        /* Header Buttons */
        .btn-header-edit {
            background: #fff;
            color: #1B3A6B;
            border: none;
            border-radius: 9px;
            padding: 9px 16px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-header-edit:hover {
            background: #f8f9fa;
            color: #1B3A6B;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
        }

        .btn-header-back {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 9px;
            padding: 9px 16px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-header-back:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
        }

        /* Main Card */
        .details-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.04);
        }

        .details-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #edf0f2;
        }

        .details-card-header h6 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .details-card-header p {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0;
        }

        .details-card-body {
            padding: 26px 24px;
        }

        /* Role Profile */
        .role-profile {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #f8faff;
            border: 1px solid #e5ecf8;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 26px;
        }

        .role-profile-icon {
            width: 52px;
            height: 52px;
            border-radius: 13px;
            background: #e8f0ff;
            color: #1B3A6B;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .role-profile-label {
            font-size: 0.72rem;
            color: #7a8491;
            margin-bottom: 3px;
        }

        .role-profile-name {
            font-size: 1.05rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 5px;
        }

        .role-name-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #fff;
            border: 1px solid #dce4ef;
            color: #1B3A6B;
            border-radius: 6px;
            padding: 4px 8px;
            font-family: monospace;
            font-size: 0.72rem;
        }

        /* Information Items */
        .info-item {
            padding: 18px 0;
            border-bottom: 1px solid #f0f1f3;
        }

        .info-item:first-child {
            padding-top: 0;
        }

        .info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #7a8491;
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .info-label i {
            font-size: 0.85rem;
            color: #1B3A6B;
        }

        .info-value {
            color: #343a40;
            font-size: 0.88rem;
        }

        .info-value strong {
            font-weight: 600;
        }

        .description-value {
            line-height: 1.6;
            color: #5f6873;
        }

        /* User Count */
        .users-count-box {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #eef6ff;
            border: 1px solid #d7e8fb;
            color: #1B3A6B;
            border-radius: 9px;
            padding: 8px 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .users-count-box i {
            font-size: 0.95rem;
        }

        /* Date Box */
        .date-value {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #5f6873;
        }

        .date-value i {
            color: #8a94a3;
        }

        /* Sidebar Cards */
        .summary-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .summary-card-header {
            padding: 20px;
            border-bottom: 1px solid #edf0f2;
        }

        .summary-card-header h6 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .summary-card-header p {
            font-size: 0.76rem;
            color: #6c757d;
            margin-bottom: 0;
        }

        .summary-card-body {
            padding: 20px;
        }

        .stat-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 13px;
            margin-bottom: 10px;
        }

        .stat-box:last-child {
            margin-bottom: 0;
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon.blue {
            background: #e8f0ff;
            color: #1B3A6B;
        }

        .stat-icon.green {
            background: #e9f8ef;
            color: #198754;
        }

        .stat-icon.gray {
            background: #eef0f2;
            color: #6c757d;
        }

        .stat-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #343a40;
        }

        .stat-label {
            font-size: 0.7rem;
            color: #7a8491;
        }

        /* Help Box */
        .help-box {
            margin-top: 16px;
            background: #f8faff;
            border: 1px solid #e0e9f8;
            border-radius: 12px;
            padding: 16px;
        }

        .help-box-title {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #1B3A6B;
            margin-bottom: 6px;
        }

        .help-box p {
            font-size: 0.73rem;
            color: #687383;
            line-height: 1.5;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 991px) {

            .summary-card {
                margin-top: 20px;
            }

        }

        @media (max-width: 768px) {

            .role-details-header {
                padding: 20px;
            }

            .role-details-header-content {
                align-items: flex-start;
                flex-direction: column;
            }

            .header-actions {
                width: 100%;
            }

            .header-actions a {
                flex: 1;
                text-align: center;
            }

            .details-card-body {
                padding: 22px 18px;
            }

            .role-profile {
                align-items: flex-start;
            }

        }
    </style>


    <div class="container-fluid role-details-page">

        {{-- ================================
             Page Header
        ================================= --}}
        <div class="role-details-header">

            <div class="role-details-header-content">

                <div class="d-flex align-items-center gap-3">

                    <div class="header-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>

                    <div>

                        <h4 class="mb-1">
                            Role Details
                        </h4>

                        <p class="mb-0">
                            View information and usage details for this role.
                        </p>

                    </div>

                </div>


                <div class="d-flex gap-2 header-actions">

                    <a href="{{ route('roles.edit', $role) }}"
                       class="btn btn-header-edit">

                        <i class="bi bi-pencil me-1"></i>
                        Edit Role

                    </a>


                    <a href="{{ route('roles.index') }}"
                       class="btn btn-header-back">

                        <i class="bi bi-arrow-left me-1"></i>
                        Back

                    </a>

                </div>

            </div>

        </div>


        <div class="row g-4">

            {{-- ================================
                 Role Information
            ================================= --}}
            <div class="col-lg-8">

                <div class="details-card">

                    {{-- Card Header --}}
                    <div class="details-card-header">

                        <h6>
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            Role Information
                        </h6>

                        <p>
                            Basic information and details about this system role.
                        </p>

                    </div>


                    <div class="details-card-body">

                        {{-- Role Profile --}}
                        <div class="role-profile">

                            <div class="role-profile-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>

                            <div>

                                <div class="role-profile-label">
                                    Display Name
                                </div>

                                <div class="role-profile-name">
                                    {{ $role->display_name }}
                                </div>

                                <span class="role-name-badge">

                                    <i class="bi bi-code-square"></i>

                                    {{ $role->name }}

                                </span>

                            </div>

                        </div>


                        {{-- Description --}}
                        <div class="info-item">

                            <div class="info-label">

                                <i class="bi bi-text-paragraph"></i>

                                Description

                            </div>

                            <div class="info-value description-value">

                                {{ $role->description ?: 'No description available for this role.' }}

                            </div>

                        </div>


                        {{-- Users --}}
                        <div class="info-item">

                            <div class="info-label">

                                <i class="bi bi-people"></i>

                                Assigned Users

                            </div>

                            <div class="info-value">

                                <span class="users-count-box">

                                    <i class="bi bi-people-fill"></i>

                                    {{ $role->users_count ?? $role->users()->count() }}

                                    {{ ($role->users_count ?? $role->users()->count()) == 1 ? 'User' : 'Users' }}

                                </span>

                            </div>

                        </div>


                        {{-- Created --}}
                        <div class="info-item">

                            <div class="info-label">

                                <i class="bi bi-calendar-plus"></i>

                                Created At

                            </div>

                            <div class="info-value">

                                <span class="date-value">

                                    <i class="bi bi-calendar3"></i>

                                    {{ $role->created_at?->format('d M Y, h:i A') ?? 'N/A' }}

                                </span>

                            </div>

                        </div>


                        {{-- Updated --}}
                        <div class="info-item">

                            <div class="info-label">

                                <i class="bi bi-clock-history"></i>

                                Last Updated

                            </div>

                            <div class="info-value">

                                <span class="date-value">

                                    <i class="bi bi-calendar3"></i>

                                    {{ $role->updated_at?->format('d M Y, h:i A') ?? 'N/A' }}

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ================================
                 Sidebar
            ================================= --}}
            <div class="col-lg-4">

                <div class="summary-card">

                    <div class="summary-card-header">

                        <h6>
                            <i class="bi bi-bar-chart me-2 text-primary"></i>
                            Role Summary
                        </h6>

                        <p>
                            Quick information about this role.
                        </p>

                    </div>


                    <div class="summary-card-body">

                        {{-- Role Name --}}
                        <div class="stat-box">

                            <div class="stat-icon blue">
                                <i class="bi bi-shield"></i>
                            </div>

                            <div>

                                <div class="stat-value">
                                    {{ $role->name }}
                                </div>

                                <div class="stat-label">
                                    System Name
                                </div>

                            </div>

                        </div>


                        {{-- Users --}}
                        <div class="stat-box">

                            <div class="stat-icon green">
                                <i class="bi bi-people"></i>
                            </div>

                            <div>

                                <div class="stat-value">
                                    {{ $role->users_count ?? $role->users()->count() }}
                                </div>

                                <div class="stat-label">
                                    Assigned Users
                                </div>

                            </div>

                        </div>


                        {{-- Created --}}
                        <div class="stat-box">

                            <div class="stat-icon gray">
                                <i class="bi bi-calendar3"></i>
                            </div>

                            <div>

                                <div class="stat-value">
                                    {{ $role->created_at?->format('d M Y') ?? 'N/A' }}
                                </div>

                                <div class="stat-label">
                                    Created Date
                                </div>

                            </div>

                        </div>


                        {{-- Help --}}
                        <div class="help-box">

                            <div class="help-box-title">

                                <i class="bi bi-lightbulb"></i>

                                Role Management

                            </div>

                            <p>
                                Use the Edit button to update this role's
                                information. Changes may affect users assigned
                                to this role.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection