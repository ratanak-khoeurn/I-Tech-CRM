<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}">Employees</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('positions.*') ? 'active' : '' }}" href="{{ route('positions.index') }}">Positions</a></li>
</ul>
