<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <title>@yield('title', 'Dashboard') — {{ \App\Models\Setting::get('center_name', config('app.name')) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body>
    <div class="app-shell">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <img src="{{ asset('assets/i-tech-kh.png') }}" alt="Logo" class="sidebar-logo" height="50px"
                    width="auto">
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">Overview</div>
                <a href="{{ route('dashboard') }}"
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                <div class="nav-section">Student Services</div>
                <a href="{{ route('students.index') }}"
                    class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Students / Alumni
                </a>
                <a href="{{ route('students.pipeline') }}"
                    class="nav-link {{ request()->routeIs('students.pipeline') ? 'active' : '' }}">
                    <i class="bi bi-kanban"></i> Pipeline Board
                </a>
                @if (auth()->user()->isAdmin())
                    @php($pendingStudentChangeCount = \App\Models\StudentChangeRequest::where('status', 'pending')->count())
                    <a href="{{ route('student-change-requests.index') }}"
                        class="nav-link {{ request()->routeIs('student-change-requests.*') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-check"></i> Student Change Approvals
                        @if($pendingStudentChangeCount > 0)
                            <span class="badge bg-warning text-dark ms-auto">{{ $pendingStudentChangeCount }}</span>
                        @endif
                    </a>
                @endif

                <a href="{{ route('documents.index') }}"
                    class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                    <i class="bi bi-folder2-open"></i> Documents
                </a>
                @if (auth()->user()->canManageStudents())
                    <a href="{{ route('attendance.face') }}"
                        class="nav-link {{ request()->routeIs('attendance.face') ? 'active' : '' }}">
                        <i class="bi bi-camera-video"></i> Face Scan
                    </a>
                    <a href="{{ route('attendance.finger') }}"
                        class="nav-link {{ request()->routeIs('attendance.finger') ? 'active' : '' }}">
                        <i class="bi bi-fingerprint"></i> Finger Scan
                    </a>
                @endif

                @if (auth()->user()->isSuperAdmin())
                    <a href="{{ route('school-clubs.index') }}"
                        class="nav-link {{ request()->routeIs('school-clubs.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> School Clubs
                    </a>
                @endif

                <div class="nav-section">Engagement &amp; Alumni</div>
                <a href="{{ route('communications.index') }}"
                    class="nav-link {{ request()->routeIs('communications.*') ? 'active' : '' }}">
                    <i class="bi bi-chat-dots"></i> Communications
                </a>
                @if (auth()->user()->canManageStudents())
                    <a href="{{ route('outreach.index') }}"
                        class="nav-link {{ request()->routeIs('outreach.*') ? 'active' : '' }}">
                        <i class="bi bi-megaphone"></i> Outreach Campaigns
                    </a>
                @endif
                <a href="{{ route('follow-ups.index') }}"
                    class="nav-link {{ request()->routeIs('follow-ups.*') ? 'active' : '' }}">
                    <i class="bi bi-bell"></i> Follow-ups
                    @if (($__taskCount ?? 0) > 0)
                        <span class="badge bg-danger ms-auto">{{ $__taskCount }}</span>
                    @endif
                </a>
                <a href="{{ route('notifications.index') }}"
                    class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <i class="bi bi-inbox"></i> Notifications
                    @if (($__notifCount ?? 0) > 0)
                        <span class="badge bg-primary ms-auto">{{ $__notifCount }}</span>
                    @endif
                </a>
                <a href="{{ route('events.index') }}"
                    class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i> Alumni Events
                </a>
                <a href="{{ route('opportunities.index') }}"
                    class="nav-link {{ request()->routeIs('opportunities.*') ? 'active' : '' }}">
                    <i class="bi bi-briefcase"></i> Opportunities
                </a>

                <div class="nav-section">Finance &amp; Reporting</div>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('invoices.index') }}"
                        class="nav-link {{ request()->routeIs('invoices.*', 'payments.*', 'fee-structures.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-coin"></i> Finance
                    </a>
                @endif
                <a href="{{ route('reports.index') }}"
                    class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i> Reports
                </a>

                @if (auth()->user()->isAdmin())
                    <div class="nav-section">Academic Management</div>
                    @if (auth()->user()->isSuperAdmin())
                        <a href="{{ route('campuses.index') }}"
                            class="nav-link {{ request()->routeIs('campuses.*', 'programs.*', 'courses.*', 'course-categories.*', 'academic-years.*', 'intakes.*', 'semesters.*') ? 'active' : '' }}">
                            <i class="bi bi-diagram-3"></i> Academic Setup
                        </a>
                    @endif
                    <a href="{{ route('batches.index') }}"
                        class="nav-link {{ request()->routeIs('batches.*', 'student-movements.*') ? 'active' : '' }}">
                        <i class="bi bi-layers"></i> Batches &amp; Movements
                    </a>
                    @if (auth()->user()->canManageStudents())
                        <a href="{{ route('study-groups.index') }}"
                            class="nav-link {{ request()->routeIs('study-groups.*') ? 'active' : '' }}">
                            <i class="bi bi-people-fill"></i> Study Groups
                        </a>
                    @endif

                    <div class="nav-section">Administration</div>
                    @if (auth()->user()->isSuperAdmin())
                        <a href="{{ route('settings.index') }}"
                            class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    @endif

                    <div class="nav-section">People &amp; Access</div>
                    @if (auth()->user()->isSuperAdmin())
                        <a href="{{ route('roles.index') }}"
                            class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <i class="bi bi-person-rolodex"></i> Roles
                        </a>
                    @endif
                    <a href="{{ route('users.index') }}"
                        class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i> Staff Accounts
                    </a>
                    <a href="{{ route('employees.index') }}"
                        class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                        <i class="bi bi-person-vcard"></i> HR / Employees
                    </a>
                    @if (auth()->user()->isSuperAdmin())
                        <a href="{{ route('audit-logs.index') }}"
                            class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                            <i class="bi bi-shield-lock"></i> Audit Logs
                        </a>
                    @endif
                @endif
            </nav>
            <div class="sidebar-footer">
                <small class="text-white-50">&copy; {{ date('Y') }}
                    {{ \App\Models\Setting::get('center_name', 'Education Center') }}</small>
            </div>
        </aside>
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Main -->
        <div class="main-area">
            <header class="topbar">
                <button class="btn btn-light btn-sm d-lg-none" id="sidebarToggle" aria-label="Toggle menu">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <form class="topbar-search" action="{{ route('students.index') }}" method="GET" role="search">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" class="form-control form-control-sm"
                        placeholder="Search students by name, ID, phone, email, course, company..."
                        value="{{ request('q') }}">
                </form>
                <div class="topbar-actions">
                    @php($__myCampuses = \App\Support\CampusContext::manageableCampuses(auth()->user(), true))
                    @php($__myActiveCampusId = \App\Support\CampusContext::current(auth()->user()))
                    @php($__showCampusSwitcher = \App\Support\CampusContext::showsSwitcher(auth()->user()))
                    @if ($__showCampusSwitcher)
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm d-flex align-items-center gap-1"
                                data-bs-toggle="dropdown" aria-label="Campus">
                                <i class="bi bi-building fs-6"></i>
                                <span class="d-none d-md-inline small fw-semibold">{{ $__myCampuses->firstWhere('id', $__myActiveCampusId)?->name ?? 'All campuses' }}</span>
                                <i class="bi bi-chevron-down small"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-header small">Switch campus</span></li>
                                @foreach ($__myCampuses as $campus)
                                    <li>
                                        <form action="{{ route('campus.switch') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="campus_id" value="{{ $campus->id }}">
                                            <button type="submit" class="dropdown-item small {{ (int) $campus->id === $__myActiveCampusId ? 'active fw-semibold' : '' }}">
                                                <i class="bi bi-{{ (int) $campus->id === $__myActiveCampusId ? 'check-circle' : 'circle' }} me-2"></i>{{ $campus->name }}
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                                @if (count(\App\Support\CampusContext::assignable(auth()->user())) > 1)
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="{{ route('campus.switch') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="campus_id" value="all">
                                            <button type="submit" class="dropdown-item small {{ $__myActiveCampusId === null ? 'active fw-semibold' : '' }}">
                                                <i class="bi bi-collection me-2"></i>{{ \App\Support\CampusContext::isBounded() ? 'All my campuses' : 'All campuses' }}
                                            </button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @elseif (\App\Support\CampusContext::isBounded(auth()->user()) && $__myCampuses->isEmpty())
                        <a href="{{ route('profile.index') }}" class="btn btn-outline-warning btn-sm"
                            title="Your account has no campus yet, so no campus data is visible.">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span class="d-none d-md-inline">No campus assigned</span>
                        </a>
                    @endif
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm position-relative" data-bs-toggle="dropdown"
                            aria-label="Notifications">
                            <i class="bi bi-bell fs-6"></i>
                            @if (($__notifCount ?? 0) > 0)
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $__notifCount > 9 ? '9+' : $__notifCount }}</span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                            <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                                Notifications
                                @if (($__notifCount ?? 0) > 0)
                                    <form action="{{ route('notifications.read-all') }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button class="btn btn-link btn-sm p-0 text-decoration-none"
                                            data-confirm="You are about to mark all notifications as read. Would you like to proceed?">Mark all
                                            read</button>
                                    </form>
                                @endif
                            </h6>
                            @forelse($__recentNotifications ?? [] as $n)
                                <a class="dropdown-item small {{ $n->is_read ? 'text-muted' : 'fw-semibold' }}"
                                    href="{{ $n->link ? url($n->link) : route('notifications.index') }}">
                                    <i class="bi bi-dot"></i>{{ $n->title }}
                                    <div class="text-muted fw-normal" style="font-size:.72rem">
                                        {{ $n->created_at->diffForHumans() }}</div>
                                </a>
                            @empty
                                <div class="dropdown-item small text-muted">No new notifications</div>
                            @endforelse
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item small text-center" href="{{ route('notifications.index') }}">View
                                all notifications</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm d-flex align-items-center gap-2"
                            data-bs-toggle="dropdown">
                            @if (auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                                    alt="{{ auth()->user()->name }}" class="rounded-circle" width="28"
                                    height="28" style="object-fit: cover;">
                            @else
                                <span
                                    class="avatar-circle">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            @endif
                            <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                            <i class="bi bi-chevron-down small"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.index') }}">
                                    <i class="bi bi-person-badge me-2"></i>View my profile
                                </a>
                            </li>
                            <li>
                                <span class="dropdown-item-text small text-muted"> <i class="bi bi-person-circle"></i>
                                    <strong>{{ auth()->user()->role?->display_name }}</strong></span>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i
                                            class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                @endif
                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i>{{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                @endif
                @if ($errors->any() && !isset($hideErrorSummary))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="bi bi-exclamation-triangle me-1"></i>Please fix the following:</strong>
                        <ul class="mb-0 mt-1 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
    </div>

    <div class="modal fade" id="globalConfirmModal" tabindex="-1" aria-labelledby="globalConfirmModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="globalConfirmModalTitle"><i
                            class="bi bi-exclamation-circle text-warning me-2" id="globalConfirmModalIcon"></i><span
                            id="globalConfirmModalTitleText">Confirm Action</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="globalConfirmModalMessage">Are you sure you want to continue?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="globalConfirmModalSubmit">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="crmToasts" aria-live="polite" aria-atomic="true"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>
