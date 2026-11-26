<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('campuses.*') ? 'active' : '' }}" href="{{ route('campuses.index') }}">Campuses</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('programs.*') ? 'active' : '' }}" href="{{ route('programs.index') }}">Programs</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('academic-years.*') ? 'active' : '' }}" href="{{ route('academic-years.index') }}">Academic Year</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('course-categories.*') ? 'active' : '' }}" href="{{ route('course-categories.index') }}">Categories</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}" href="{{ route('courses.index') }}">Courses</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('batches.*') ? 'active' : '' }}" href="{{ route('batches.index') }}">Batches</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('intakes.*') ? 'active' : '' }}" href="{{ route('intakes.index') }}">Intakes</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('semesters.*') ? 'active' : '' }}" href="{{ route('semesters.index') }}">Semesters</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('student-movements.*') ? 'active' : '' }}" href="{{ route('student-movements.index') }}">Student Movements</a></li>
</ul>
