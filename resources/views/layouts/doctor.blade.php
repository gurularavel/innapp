<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — InnApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #0f4c75;
            flex-shrink: 0;
            transition: width .25s ease, transform .25s ease;
            overflow: hidden;
        }
        .sidebar .nav-link { color: #b0c4de; padding: .6rem 1.25rem; border-radius: .375rem; margin: 2px 8px; white-space: nowrap; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #1b6ca8; color: #fff; }
        .sidebar .nav-link i { width: 20px; flex-shrink: 0; }
        .sidebar-brand { padding: 1.25rem; border-bottom: 1px solid #1b6ca8; white-space: nowrap; }

        /* ── Collapsed state ── */
        .sidebar-collapsed .sidebar {
            width: 60px;
        }
        .sidebar-collapsed .sidebar .nav-link span,
        .sidebar-collapsed .sidebar .sidebar-brand .brand-text,
        .sidebar-collapsed .sidebar .sidebar-brand .brand-sub,
        .sidebar-collapsed .sidebar .sub-info,
        .sidebar-collapsed .sidebar .user-name,
        .sidebar-collapsed .sidebar .logout-btn span {
            display: none;
        }
        .sidebar-collapsed .sidebar .nav-link {
            display: flex;
            justify-content: center;
            padding: .6rem 0;
            margin: 2px 6px;
        }
        .sidebar-collapsed .sidebar .nav-link i { width: auto; }
        .sidebar-collapsed .sidebar .sidebar-brand {
            display: flex;
            justify-content: center;
            padding: 1.1rem .5rem;
        }
        .sidebar-collapsed .sidebar .sub-info-block { display: none; }
        .sidebar-collapsed .sidebar .logout-btn { padding: .4rem; justify-content: center; }
        .sidebar-collapsed .sidebar .bottom-section { padding: .5rem; }

        /* ── Main content ── */
        .main-content { flex: 1; overflow-x: hidden; min-width: 0; }
        .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; }

        /* ── Toggle button ── */
        #sidebar-toggle {
            background: none;
            border: none;
            color: #64748b;
            padding: .3rem .4rem;
            border-radius: .375rem;
            cursor: pointer;
            font-size: 1.2rem;
            line-height: 1;
            transition: background .15s;
        }
        #sidebar-toggle:hover { background: #f1f5f9; color: #0f4c75; }

        /* ── Tooltip on collapsed links ── */
        .sidebar-collapsed .sidebar .nav-link { position: relative; }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex" id="app-wrapper">

    <!-- Sidebar -->
    <nav class="sidebar d-flex flex-column" id="sidebar">
        <div class="sidebar-brand d-flex align-items-center gap-2">
            <a href="{{ route('panel.dashboard') }}" class="text-white text-decoration-none fw-bold fs-5 d-flex align-items-center gap-2">
                <i class="bi bi-grid-fill flex-shrink-0"></i>
                <span class="brand-text">InnApp</span>
            </a>
            <div class="text-info small mt-0 brand-sub ms-1" style="font-size:.78rem">
                {{ auth()->user()->specialty?->name ?? 'İstifadəçi' }}
            </div>
        </div>

        <ul class="nav flex-column mt-3 flex-grow-1">
            <li class="nav-item">
                <a href="{{ route('panel.dashboard') }}" class="nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" title="Dashboard">
                    <i class="bi bi-speedometer2 me-2"></i><span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('panel.patients.index') }}" class="nav-link {{ request()->routeIs('doctor.patients*') ? 'active' : '' }}" title="Müştərilər">
                    <i class="bi bi-people me-2"></i><span>Müştərilər</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('panel.appointments.index') }}" class="nav-link {{ request()->routeIs('doctor.appointments*') ? 'active' : '' }}" title="Randevular">
                    <i class="bi bi-calendar-check me-2"></i><span>Randevular</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('panel.calendar.index') }}" class="nav-link {{ request()->routeIs('doctor.calendar*') ? 'active' : '' }}" title="Təqvim">
                    <i class="bi bi-calendar3 me-2"></i><span>Təqvim</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('panel.treatment-types.index') }}" class="nav-link {{ request()->routeIs('doctor.treatment-types*') ? 'active' : '' }}" title="Xidmət Növləri">
                    <i class="bi bi-list-check me-2"></i><span>Xidmət Növləri</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('panel.subscription.index') }}" class="nav-link {{ request()->routeIs('doctor.subscription*') ? 'active' : '' }}" title="Abunəlik">
                    <i class="bi bi-shield-check me-2"></i><span>Abunəlik</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('panel.reports.revenue') }}" class="nav-link {{ request()->routeIs('doctor.reports*') ? 'active' : '' }}" title="Hesabat">
                    <i class="bi bi-bar-chart-line me-2"></i><span>Hesabat</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('panel.profile.edit') }}" class="nav-link {{ request()->routeIs('doctor.profile*') ? 'active' : '' }}" title="Profil">
                    <i class="bi bi-person-circle me-2"></i><span>Profil</span>
                </a>
            </li>
        </ul>

        @php $subscription = auth()->user()->activeSubscription()->with('package')->first(); @endphp
        @if($subscription)
        <div class="sub-info-block p-3 mx-2 mb-2 rounded" style="background: rgba(255,255,255,0.1)">
            <div class="sub-info text-white small fw-semibold">{{ $subscription->package->name }}</div>
            <div class="sub-info text-info small">
                Müştəri: {{ $subscription->patients_used }}/{{ $subscription->package->patient_limit ?? '∞' }}
            </div>
            <div class="sub-info text-info small">
                SMS: {{ $subscription->sms_used }}/{{ $subscription->package->sms_limit ?? '∞' }}
            </div>
            <div class="sub-info text-warning small">Son: {{ $subscription->expires_at->format('d.m.Y') }}</div>
        </div>
        @endif

        <div class="bottom-section p-3 border-top" style="border-color: #1b6ca8 !important">
            <div class="user-name text-info small mb-2">{{ auth()->user()->full_name }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn btn btn-sm btn-outline-light w-100 d-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-box-arrow-right flex-shrink-0"></i>
                    <span>Çıxış</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar px-4 py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button id="sidebar-toggle" title="Menunu gizlət / göstər">
                    <i class="bi bi-list" id="toggle-icon"></i>
                </button>
                <h5 class="mb-0 fw-semibold">@yield('page-title', 'Dashboard')</h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary">İstifadəçi</span>
                <span class="text-muted small">{{ auth()->user()->full_name }}</span>
            </div>
        </div>

        <div class="p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const wrapper = document.getElementById('app-wrapper');
    const icon    = document.getElementById('toggle-icon');
    const KEY     = 'sidebar_collapsed';

    function apply(collapsed) {
        wrapper.classList.toggle('sidebar-collapsed', collapsed);
        icon.className = collapsed ? 'bi bi-layout-sidebar' : 'bi bi-list';
    }

    // Restore saved state
    apply(localStorage.getItem(KEY) === '1');

    document.getElementById('sidebar-toggle').addEventListener('click', function () {
        const next = !wrapper.classList.contains('sidebar-collapsed');
        apply(next);
        localStorage.setItem(KEY, next ? '1' : '0');
    });
})();
</script>
@stack('scripts')
</body>
</html>
