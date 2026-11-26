@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')

    <style>
        /* ================================
           Edit Role Page
        ================================= */

        .edit-role-page {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .edit-role-header {
            background: linear-gradient(135deg, #1B3A6B 0%, #274d88 100%);
            border-radius: 16px;
            padding: 24px 28px;
            color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(27, 58, 107, 0.12);
        }

        .edit-role-header-content {
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

        .edit-role-header h4 {
            font-size: 1.35rem;
            font-weight: 600;
        }

        .edit-role-header p {
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

        /* Role Status */
        .current-role-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 24px;
        }

        .current-role-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .current-role-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: #e8f0ff;
            color: #1B3A6B;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .current-role-label {
            font-size: 0.7rem;
            color: #7a8491;
            margin-bottom: 2px;
        }

        .current-role-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #343a40;
        }

        .current-role-badge {
            background: #eef4ff;
            color: #1B3A6B;
            border: 1px solid #d8e5ff;
            border-radius: 20px;
            padding: 5px 10px;
            font-size: 0.72rem;
            font-weight: 600;
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

        .btn-update {
            background: #1B3A6B;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 18px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-update:hover {
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

        /* Warning Box */
        .warning-box {
            margin-top: 18px;
            background: #fff8e8;
            border: 1px solid #ffe4a8;
            border-radius: 10px;
            padding: 14px;
        }

        .warning-box-title {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 0.76rem;
            font-weight: 700;
            color: #946200;
            margin-bottom: 5px;
        }

        .warning-box p {
            font-size: 0.74rem;
            color: #856404;
            line-height: 1.5;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 991px) {

            .guidelines-card {
                margin-top: 20px;
            }

        }

        @media (max-width: 768px) {

            .edit-role-header {
                padding: 20px;
            }

            .edit-role-header-content {
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
            .btn-update {
                flex: 1;
            }

            .current-role-box {
                align-items: flex-start;
                flex-direction: column;
            }

        }
    </style>


    <div class="container-fluid edit-role-page">

        {{-- ================================
             Page Header
        ================================= --}}
        <div class="edit-role-header">

            <div class="edit-role-header-content">

                <div class="d-flex align-items-center gap-3">

                    <div class="header-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>

                    <div>

                        <h4 class="mb-1">
                            Edit Role
                        </h4>

                        <p class="mb-0">
                            Update the information for this system role.
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
                 Edit Form
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
                            Update the role details below and save your changes.
                        </p>

                    </div>


                    <form action="{{ route('roles.update', $role) }}"
                          method="POST">

                        @csrf
                        @method('PUT')


                        <div class="role-form-body">

                            {{-- Current Role --}}
                            <div class="current-role-box">

                                <div class="current-role-info">

                                    <div class="current-role-icon">
                                        <i class="bi bi-shield-check"></i>
                                    </div>

                                    <div>

                                        <div class="current-role-label">
                                            Currently editing
                                        </div>

                                        <div class="current-role-name">
                                            {{ $role->display_name }}
                                        </div>

                                    </div>

                                </div>


                                <span class="current-role-badge">
                                    {{ $role->name }}
                                </span>

                            </div>


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
                                        value="{{ old('name', $role->name) }}"
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

                                    Use a unique system name.

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
                                        value="{{ old('display_name', $role->display_name) }}"
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
                                    This is the name displayed to users in the system.
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
                                    >{{ old('description', $role->description) }}</textarea>

                                </div>


                                @error('description')

                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>

                                @enderror


                                <div class="field-footer">

                                    <div class="form-help mt-0">
                                        Optional. Add a short explanation of this role.
                                    </div>

                                    <span class="character-count" id="descriptionCounter">
                                        {{ strlen(old('description', $role->description ?? '')) }} / 500
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
                                    class="btn btn-update">

                                <i class="bi bi-check-lg me-1"></i>
                                Update Role

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
                            Editing Guidelines
                        </h6>

                        <p>
                            Keep role information clear and consistent.
                        </p>

                    </div>


                    <div class="guidelines-body">

                        {{-- Guideline 1 --}}
                        <div class="guideline-item">

                            <div class="guideline-icon">
                                <i class="bi bi-pencil"></i>
                            </div>

                            <div class="guideline-content">

                                <strong>
                                    Review before saving
                                </strong>

                                <span>
                                    Make sure the role information is correct before updating.
                                </span>

                            </div>

                        </div>


                        {{-- Guideline 2 --}}
                        <div class="guideline-item">

                            <div class="guideline-icon">
                                <i class="bi bi-key"></i>
                            </div>

                            <div class="guideline-content">

                                <strong>
                                    Keep system names consistent
                                </strong>

                                <span>
                                    Use lowercase names such as
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
                                    Check assigned users
                                </strong>

                                <span>
                                    Consider users assigned to this role before changing its purpose.
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
                                    Keep responsibilities clear
                                </strong>

                                <span>
                                    The role description should clearly explain its purpose.
                                </span>

                            </div>

                        </div>


                        {{-- Warning --}}
                        <div class="warning-box">

                            <div class="warning-box-title">

                                <i class="bi bi-exclamation-triangle"></i>

                                Important

                            </div>

                            <p>
                                Changing a role name may affect how the role is referenced
                                elsewhere in your application.
                            </p>

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