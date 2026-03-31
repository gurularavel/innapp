<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — InnApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
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

        /* ── Mobile sidebar overlay ── */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1044;
            background: rgba(0,0,0,.45);
        }
        .sidebar-backdrop.show { display: block; }

        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1045;
                transform: translateX(-100%);
                width: 260px !important;
                box-shadow: 4px 0 20px rgba(0,0,0,.25);
            }
            .sidebar.mobile-open { transform: translateX(0); }
            #app-wrapper { display: block !important; }
            .main-content { width: 100%; }
            .content-padding { padding: .75rem !important; }
            .topbar { padding-left: .75rem !important; padding-right: .75rem !important; }
            .topbar .text-muted.small { display: none; }
        }
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
                <a href="{{ route('panel.sms-templates.index') }}" class="nav-link {{ request()->routeIs('panel.sms-templates*') ? 'active' : '' }}" title="SMS Şablonları">
                    <i class="bi bi-chat-dots me-2"></i><span>SMS Şablonları</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('panel.profile.edit') }}" class="nav-link {{ request()->routeIs('panel.profile*') ? 'active' : '' }}" title="Profil">
                    <i class="bi bi-person-circle me-2"></i><span>Profil</span>
                </a>
            </li>
        </ul>

        @php $subscription = auth()->user()->activeSubscription()->with('package')->first(); @endphp
        @if($subscription && !auth()->user()->is_demo)
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

    <!-- Mobile sidebar backdrop -->
    <div id="sidebar-backdrop" class="sidebar-backdrop"></div>

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

        @if(auth()->user()->is_demo)
        @php $remaining = now()->diffInMinutes(auth()->user()->demo_expires_at, false); @endphp
        <div style="background:linear-gradient(90deg,#e76f51,#f4a261);padding:10px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div class="d-flex align-items-center gap-2" style="color:#fff;font-size:.88rem;font-weight:600">
                <i class="bi bi-play-circle-fill"></i>
                Demo rejimi —
                @if($remaining > 0)
                    <span id="demo-timer" data-minutes="{{ $remaining }}">{{ $remaining }} dəqiqə qalıb</span>
                @else
                    <span>Müddət bitib</span>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                <span style="color:rgba(255,255,255,.8);font-size:.8rem">Bəyəndiniz? Öz hesabınızı yaradın</span>
                <form method="POST" action="{{ route('demo.exit') }}" style="margin:0">
                    @csrf
                    <button type="submit" style="background:#fff;color:#e76f51;font-weight:700;font-size:.82rem;padding:5px 14px;border-radius:6px;border:none;cursor:pointer">
                        <i class="bi bi-rocket-takeoff me-1"></i>Qeydiyyat
                    </button>
                </form>
            </div>
        </div>
        @endif

        <div class="p-4 content-padding">
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
<script src="https://cdn.jsdelivr.net/npm/imask@7.6.1/dist/imask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
// Global flatpickr config – Monday as first day
flatpickr.localize({ firstDayOfWeek: 1 });
document.addEventListener('DOMContentLoaded', function () {
    flatpickr('input[type="date"]', {
        dateFormat: 'Y-m-d',
        locale: { firstDayOfWeek: 1 },
        allowInput: true,
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[name="phone"]').forEach(function (el) {
        // Normalize existing value to 994XXXXXXXXX before applying mask
        var digits = el.value.replace(/\D/g, '');
        if (digits.startsWith('0') && digits.length === 10) digits = '994' + digits.slice(1);
        else if (digits.length === 9) digits = '994' + digits;
        el.value = digits ? '+' + digits : '';

        IMask(el, {
            mask: '+{994} 00 000 00 00',
            lazy: false,
            placeholderChar: '_'
        });
    });
});
</script>
<script>
(function () {
    const wrapper  = document.getElementById('app-wrapper');
    const sidebar  = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const icon     = document.getElementById('toggle-icon');
    const KEY      = 'sidebar_collapsed';

    function isMobile() { return window.innerWidth < 768; }

    function applyDesktop(collapsed) {
        wrapper.classList.toggle('sidebar-collapsed', collapsed);
        icon.className = collapsed ? 'bi bi-layout-sidebar' : 'bi bi-list';
    }

    function openMobile() {
        sidebar.classList.add('mobile-open');
        backdrop.classList.add('show');
        icon.className = 'bi bi-x-lg';
    }

    function closeMobile() {
        sidebar.classList.remove('mobile-open');
        backdrop.classList.remove('show');
        icon.className = 'bi bi-list';
    }

    if (!isMobile()) {
        applyDesktop(localStorage.getItem(KEY) === '1');
    }

    document.getElementById('sidebar-toggle').addEventListener('click', function () {
        if (isMobile()) {
            sidebar.classList.contains('mobile-open') ? closeMobile() : openMobile();
        } else {
            const next = !wrapper.classList.contains('sidebar-collapsed');
            applyDesktop(next);
            localStorage.setItem(KEY, next ? '1' : '0');
        }
    });

    backdrop.addEventListener('click', closeMobile);

    window.addEventListener('resize', function () {
        if (!isMobile()) {
            closeMobile();
            applyDesktop(localStorage.getItem(KEY) === '1');
        }
    });
})();
</script>
@stack('scripts')
@if(auth()->user()?->is_demo)
<script>
(function() {
    var el = document.getElementById('demo-timer');
    if (!el) return;
    var secs = parseInt(el.dataset.minutes) * 60;
    function tick() {
        if (secs <= 0) { location.reload(); return; }
        secs--;
        var m = Math.floor(secs / 60), s = secs % 60;
        el.textContent = m + ' dəq ' + (s < 10 ? '0' : '') + s + ' san qalıb';
    }
    setInterval(tick, 1000);
})();
</script>
@endif
</body>
</html>
