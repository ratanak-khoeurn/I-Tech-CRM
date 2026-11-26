@extends('layouts.app')

@section('title', 'Create Role')

@section('content')

    <style>
        /* ================================
           Create Role Page
        ================================= */

        .create-role-page {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .create-role-header {
            background: linear-gradient(135deg, #1B3A6B 0%, #274d88 100%);
            border-radius: 16px;
            padding: 24px 28px;
            color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(27, 58, 107, 0.12);
        }

        .create-role-header-content {
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

        .create-role-header h4 {
            font-size: 1.35rem;
            font-weight: 600;
        }

        .create-role-header p {
            color: rgba(255, 255, 255, 0.75);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 9px;
            padding: 9px 16px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            transform: translateY(-1px);
        }

        /* Form Card */
        .role-form-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.04);
        }

        .role-form-header {
            padding: 20px 24px;
            border-bottom: 1px solid #edf0f2;
        }

        .role-form-header h6 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .role-form-header p {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0;
        }

        .role-form-body {
            padding: 28px 24px;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 22px;
        }

        .form-label-custom {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 8px;
        }

        .required-star {
            color: #dc3545;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #8a94a3;
            pointer-events: none;
            z-index: 2;
        }

        .textarea-icon {
            position: absolute;
            left: 13px;
            top: 14px;
            color: #8a94a3;
            pointer-events: none;
            z-index: 2;
        }

        .form-control-custom {
            min-height: 44px;
            border: 1px solid #dfe3e7;
            border-radius: 9px;
            padding-left: 40px;
            font-size: 0.88rem;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus {
            border-color: #1B3A6B;
            box-shadow: 0 0 0 3px rgba(27, 58, 107, 0.08);
        }

        textarea.form-control-custom {
            padding-top: 12px;
            padding-left: 40px;
            resize: vertical;
            min-height: 120px;
        }

        .form-control-custom.is-invalid {
            border-color: #dc3545;
        }

        .form-control-custom.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.08);
        }

        .form-help {
            margin-top: 7px;
            font-size: 0.76rem;
            color: #7a8491;
        }

        .form-help code {
            background: #f1f3f5;
            color: #1B3A6B;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 0.73rem;
        }

        /* Character Counter */
        .field-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 7px;
        }

        .character-count {
            font-size: 0.72rem;
            color: #8a94a3;
        }

        /* Form Footer */
        .role-form-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            padding: 18px 24px;
            border-top: 1px solid #edf0f2;
            background: #fafbfc;
        }

        .btn-cancel {
            border: 1px solid #dee2e6;
            background: #fff;
            color: #495057;
            border-radius: 8px;
            padding: 9px 16px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background: #f8f9fa;
            border-color: #cfd4da;
            color: #212529;
        }

        .btn-create {
            background: #1B3A6B;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 18px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-create:hover {
            background: #153058;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 5px 12px rgba(27, 58, 107, 0.18);
        }

        /* Guidelines Card */
        .guidelines-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .guidelines-header {
            padding: 20px;
            border-bottom: 1px solid #edf0f2;
        }

        .guidelines-header h6 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .guidelines-header p {
            font-size: 0.77rem;
            color: #6c757d;
            margin-bottom: 0;
        }

        .guidelines-body {
            padding: 20px;
        }

        .guideline-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f1f3;
        }

        .guideline-item:first-child {
            padding-top: 0;
        }

        .guideline-item:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }

        .guideline-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #eef4ff;
            color: #1B3A6B;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.85rem;
        }

        .guideline-content strong {
            display: block;
            font-size: 0.8rem;
            color: #343a40;
            margin-bottom: 3px;
        }

        .guideline-content span {
            display: block;
            font-size: 0.74rem;
            color: #7a8491;
            line-height: 1.5;
        }

        /* Example Box */
        .example-box {
            margin-top: 18px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 14px;
        }

        .example-box-title {
            font-size: 0.72rem;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 10px;
        }

        .example-role {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .example-role:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .example-role:first-of-type {
            padding-top: 0;
        }

        .example-system-name {
            font-family: monospace;
            font-size: 0.75rem;
            color: #1B3A6B;
        }

        .example-display-name {
            font-size: 0.74rem;
            color: #6c757d;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .guidelines-card {
                margin-top: 20px;
            }
        }

        @media (max-width: 768px) {

            .create-role-header {
                padding: 20px;
            }

            .create-role-header-content {
                align-items: flex-start;
                flex-direction: column;
            }

            .btn-back {
                width: 100%;
                text-align: center;
            }

            .role-form-body {
                padding: 22px 18px;
            }

            .role-form-footer {
                padding: 16px 18px;
            }

            .btn-cancel,
            .btn-create {
                flex: 1;
            }
        }
    </style>


    <div class="container-fluid create-role-page">

        {{-- ================================
             Page Header
        ================================= --}}
        <div class="create-role-header">

            <div class="create-role-header-content">

                <div class="d-flex align-items-center gap-3">

                    <div class="header-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>

                    <div>

                        <h4 class="mb-1">
                            Create New Role
                        </h4>

                        <p class="mb-0">
                            Add a new role and define its basic information.
                        </p>

                    </div>

                </div>


                <a href="{{ route('roles.index') }}" class="btn btn-back">

                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Roles

                </a>

            </div>

        </div>


        <div class="row g-4">

            {{-- ================================
                 Form
            ================================= --}}
            <div class="col-lg-8">

                <div class="role-form-card">

                    {{-- Form Header --}}
                    <div class="role-form-header">

                        <h6>
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            Role Information
                        </h6>

                        <p>
                            Enter the information below to create a new system role.
                        </p>

                    </div>


                    {{-- Form --}}
                    <form action="{{ route('roles.store') }}" method="POST">

                        @csrf

                        <div class="role-form-body">

                            {{-- Role Name --}}
                            <div class="form-group">

                                <label for="name" class="form-label-custom">

                                    <i class="bi bi-code-square text-muted"></i>

                                    Role Name

                                    <span class="required-star">*</span>

                                </label>


                                <div class="input-wrapper">

                                    <i class="bi bi-shield-lock input-icon"></i>

                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        value="{{ old('name') }}"
                                        class="form-control form-control-custom @error('name') is-invalid @enderror"
                                        placeholder="e.g. admin"
                                        maxlength="100"
                                        autocomplete="off"
                                        required
                                    >

                                </div>


                                @error('name')

                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>

                                @enderror


                                <div class="form-help">

                                    Use a unique system name using lowercase letters,
                                    numbers, and underscores.

                                    Examples:
                                    <code>admin</code>
                                    <code>teacher</code>
                                    <code>school_admin</code>

                                </div>

                            </div>


                            {{-- Display Name --}}
                            <div class="form-group">

                                <label for="display_name" class="form-label-custom">

                                    <i class="bi bi-card-text text-muted"></i>

                                    Display Name

                                    <span class="required-star">*</span>

                                </label>


                                <div class="input-wrapper">

                                    <i class="bi bi-person-badge input-icon"></i>

                                    <input
                                        type="text"
                                        id="display_name"
                                        name="display_name"
                                        value="{{ old('display_name') }}"
                                        class="form-control form-control-custom @error('display_name') is-invalid @enderror"
                                        placeholder="e.g. School Administrator"
                                        maxlength="150"
                                        required
                                    >

                                </div>


                                @error('display_name')

                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>

                                @enderror


                                <div class="form-help">
                                    This is the friendly name that users will see in the system.
                                </div>

                            </div>


                            {{-- Description --}}
                            <div class="form-group mb-0">

                                <label for="description" class="form-label-custom">

                                    <i class="bi bi-text-paragraph text-muted"></i>

                                    Description

                                </label>


                                <div class="input-wrapper">

                                    <i class="bi bi-chat-left-text textarea-icon"></i>

                                    <textarea
                                        id="description"
                                        name="description"
                                        rows="5"
                                        maxlength="500"
                                        class="form-control form-control-custom @error('description') is-invalid @enderror"
                                        placeholder="Describe what this role is responsible for..."
                                    >{{ old('description') }}</textarea>

                                </div>


                                @error('description')

                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>

                                @enderror


                                <div class="field-footer">

                                    <div class="form-help mt-0">
                                        Optional. A short description helps administrators understand the role.
                                    </div>

                                    <span class="character-count" id="descriptionCounter">
                                        0 / 500
                                    </span>

                                </div>

                            </div>

                        </div>


                        {{-- Form Footer --}}
                        <div class="role-form-footer">

                            <a href="{{ route('roles.index') }}"
                               class="btn btn-cancel">

                                <i class="bi bi-x-lg me-1"></i>
                                Cancel

                            </a>


                            <button type="submit"
                                    class="btn btn-create">

                                <i class="bi bi-check-lg me-1"></i>
                                Create Role

                            </button>

                        </div>

                    </form>

                </div>

            </div>


            {{-- ================================
                 Guidelines
            ================================= --}}
            <div class="col-lg-4">

                <div class="guidelines-card">

                    <div class="guidelines-header">

                        <h6>
                            <i class="bi bi-lightbulb me-2 text-warning"></i>
                            Role Guidelines
                        </h6>

                        <p>
                            Keep your role names clear and consistent.
                        </p>

                    </div>


                    <div class="guidelines-body">

                        {{-- Guideline 1 --}}
                        <div class="guideline-item">

                            <div class="guideline-icon">
                                <i class="bi bi-check2"></i>
                            </div>

                            <div class="guideline-content">

                                <strong>
                                    Use clear names
                                </strong>

                                <span>
                                    Choose a name that clearly identifies the user's role.
                                </span>

                            </div>

                        </div>


                        {{-- Guideline 2 --}}
                        <div class="guideline-item">

                            <div class="guideline-icon">
                                <i class="bi bi-type"></i>
                            </div>

                            <div class="guideline-content">

                                <strong>
                                    Use lowercase system names
                                </strong>

                                <span>
                                    Use names such as
                                    <code>admin</code> or
                                    <code>teacher</code>.
                                </span>

                            </div>

                        </div>


                        {{-- Guideline 3 --}}
                        <div class="guideline-item">

                            <div class="guideline-icon">
                                <i class="bi bi-people"></i>
                            </div>

                            <div class="guideline-content">

                                <strong>
                                    Think about responsibilities
                                </strong>

                                <span>
                                    Create roles based on what users need to access.
                                </span>

                            </div>

                        </div>


                        {{-- Guideline 4 --}}
                        <div class="guideline-item">

                            <div class="guideline-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>

                            <div class="guideline-content">

                                <strong>
                                    Keep access controlled
                                </strong>

                                <span>
                                    Only give a role the permissions required for its responsibilities.
                                </span>

                            </div>

                        </div>


                        {{-- Examples --}}
                        <div class="example-box">

                            <div class="example-box-title">
                                Example Roles
                            </div>


                            <div class="example-role">

                                <span class="example-system-name">
                                    admin
                                </span>

                                <span class="example-display-name">
                                    Administrator
                                </span>

                            </div>


                            <div class="example-role">

                                <span class="example-system-name">
                                    school_admin
                                </span>

                                <span class="example-display-name">
                                    School Administrator
                                </span>

                            </div>


                            <div class="example-role">

                                <span class="example-system-name">
                                    teacher
                                </span>

                                <span class="example-display-name">
                                    Teacher
                                </span>

                            </div>


                            <div class="example-role">

                                <span class="example-system-name">
                                    coordinator
                                </span>

                                <span class="example-display-name">
                                    Coordinator
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Character Counter --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const description = document.getElementById('description');
            const counter = document.getElementById('descriptionCounter');

            if (description && counter) {

                function updateCounter() {
                    counter.textContent = description.value.length + ' / 500';
                }

                updateCounter();

                description.addEventListener('input', updateCounter);
            }

        });
    </script>

@endsection