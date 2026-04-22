<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — InnApp</title>
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon/favicon-96x96.png">
    <link rel="apple-touch-icon" href="/favicon/apple-touch-icon.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --sb-bg: #0d2c54;
            --sb-brand-bg: #0a2245;
            --sb-active: #1a85d9;
            --sb-active-bg: rgba(26,133,217,.14);
            --sb-hover-bg: rgba(255,255,255,.06);
            --sb-text: #8fb3d9;
            --sb-text-active: #ffffff;
            --sb-border: rgba(255,255,255,.07);
            --accent: #1a85d9;
            --accent-hover: #1570bb;
            --body-bg: #eef2f7;
            --topbar-bg: #ffffff;
            --topbar-shadow: 0 1px 0 #e2e9f3, 0 2px 8px rgba(13,44,84,.05);
            --card-shadow: 0 2px 12px rgba(13,44,84,.07);
        }

        /* Tom Select: dropdown açıq olanda item-i tam gizlət */
        .ts-wrapper.single.dropdown-active .ts-control > .item { display: none !important; }

        * { box-sizing: border-box; }
        body { background-color: var(--body-bg); font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--sb-bg);
            flex-shrink: 0;
            transition: width .25s ease, transform .25s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 12px rgba(0,0,0,.18);
        }

        .sidebar-brand {
            background: var(--sb-brand-bg);
            padding: 1.2rem 1.25rem;
            border-bottom: 1px solid var(--sb-border);
            white-space: nowrap;
            flex-shrink: 0;
        }
        .sidebar-brand .brand-icon {
            width: 34px; height: 34px;
            background: var(--accent);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
            color: #fff;
            box-shadow: 0 2px 8px rgba(26,133,217,.4);
        }
        .sidebar-brand .brand-text { color: #fff; font-weight: 700; font-size: 1.05rem; letter-spacing: .01em; }
        .sidebar-brand .brand-sub { color: var(--sb-text); font-size: .72rem; margin-top: 1px; }

        /* nav items */
        .sidebar ul.nav { padding: .75rem 0; }
        .sidebar .nav-link {
            color: var(--sb-text);
            padding: .55rem 1rem;
            margin: 1px .75rem;
            border-radius: .45rem;
            display: flex; align-items: center;
            white-space: nowrap;
            font-size: .875rem;
            font-weight: 500;
            transition: background .15s, color .15s, border-color .15s;
            border-left: 2px solid transparent;
        }
        .sidebar .nav-link i { width: 20px; font-size: 1rem; flex-shrink: 0; color: var(--sb-text); transition: color .15s; }
        .sidebar .nav-link:hover {
            background: var(--sb-hover-bg);
            color: #fff;
        }
        .sidebar .nav-link:hover i { color: #fff; }
        .sidebar .nav-link.active {
            background: var(--sb-active-bg);
            color: var(--sb-text-active);
            border-left-color: var(--sb-active);
        }
        .sidebar .nav-link.active i { color: var(--sb-active); }

        /* subscription block */
        .sub-info-block {
            margin: 0 .75rem .75rem;
            background: rgba(26,133,217,.1);
            border: 1px solid rgba(26,133,217,.2);
            border-radius: .5rem;
            padding: .75rem 1rem;
            flex-shrink: 0;
        }
        .sub-info-block .sub-pkg { color: #fff; font-size: .82rem; font-weight: 600; margin-bottom: .3rem; }
        .sub-info-block .sub-row { color: var(--sb-text); font-size: .76rem; display: flex; justify-content: space-between; margin-top: .15rem; }
        .sub-info-block .sub-exp { color: #f0a500; font-size: .74rem; margin-top: .3rem; }

        /* bottom section */
        .bottom-section {
            padding: .85rem 1rem;
            border-top: 1px solid var(--sb-border);
            flex-shrink: 0;
        }
        .user-name { color: var(--sb-text); font-size: .8rem; font-weight: 500; margin-bottom: .5rem; display: flex; align-items: center; gap: .5rem; }
        .user-name::before {
            content: '';
            width: 7px; height: 7px;
            background: #2ecc71;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .logout-btn {
            background: rgba(255,255,255,.07) !important;
            border: 1px solid rgba(255,255,255,.12) !important;
            color: var(--sb-text) !important;
            font-size: .82rem !important;
            padding: .38rem .75rem !important;
            border-radius: .45rem !important;
            transition: background .15s, color .15s !important;
        }
        .logout-btn:hover { background: rgba(255,255,255,.14) !important; color: #fff !important; }

        /* ── Collapsed state ── */
        .sidebar-collapsed .sidebar { width: 60px; }
        .sidebar-collapsed .sidebar .nav-link span,
        .sidebar-collapsed .sidebar .sidebar-brand .brand-text,
        .sidebar-collapsed .sidebar .sidebar-brand .brand-sub,
        .sidebar-collapsed .sidebar .sub-info,
        .sidebar-collapsed .sidebar .user-name,
        .sidebar-collapsed .sidebar .logout-btn span { display: none; }
        .sidebar-collapsed .sidebar .nav-link {
            justify-content: center;
            padding: .55rem 0;
            margin: 1px 6px;
            border-left-color: transparent !important;
        }
        .sidebar-collapsed .sidebar .nav-link i { width: auto; }
        .sidebar-collapsed .sidebar .sidebar-brand { justify-content: center; padding: 1.1rem .5rem; }
        .sidebar-collapsed .sidebar .sub-info-block { display: none; }
        .sidebar-collapsed .sidebar .logout-btn { padding: .4rem !important; justify-content: center; }
        .sidebar-collapsed .sidebar .bottom-section { padding: .5rem; }

        /* ── Main content ── */
        .main-content { flex: 1; overflow-x: hidden; min-width: 0; }

        /* ── Topbar ── */
        .topbar {
            background: var(--topbar-bg);
            box-shadow: var(--topbar-shadow);
            position: sticky; top: 0; z-index: 100;
        }
        .topbar .page-title { font-size: 1rem; font-weight: 600; color: #1a2e4a; }
        .topbar .user-badge {
            background: var(--accent);
            color: #fff;
            font-size: .72rem;
            padding: .22rem .65rem;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: .02em;
        }
        .topbar .user-label { color: #4a6080; font-size: .83rem; font-weight: 500; }

        /* ── Toggle button ── */
        #sidebar-toggle {
            background: none;
            border: none;
            color: #64748b;
            padding: .35rem .45rem;
            border-radius: .45rem;
            cursor: pointer;
            font-size: 1.2rem;
            line-height: 1;
            transition: background .15s, color .15s;
        }
        #sidebar-toggle:hover { background: #eef2f7; color: var(--accent); }

        /* ── Cards & content ── */
        .card {
            border: none !important;
            box-shadow: var(--card-shadow);
            border-radius: .65rem !important;
        }
        .card-header {
            background: #fff !important;
            border-bottom: 1px solid #e8edf4 !important;
            font-weight: 600;
            color: #1a2e4a;
            padding: .85rem 1.25rem;
        }
        .card-footer { background: #fff !important; }

        /* ── Tables ── */
        .table { font-size: .875rem; }
        .table thead th {
            background: #f4f7fc;
            color: #4a6080;
            font-weight: 600;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: none;
            padding: .7rem 1rem;
        }
        .table tbody td { padding: .75rem 1rem; vertical-align: middle; color: #2c3e50; }
        .table-hover tbody tr:hover { background-color: #f8fafd !important; }

        /* ── Stat cards ── */
        .stat-card { border-radius: .65rem !important; border: none !important; box-shadow: var(--card-shadow); overflow: hidden; }
        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: .5rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; color: #1a2e4a; line-height: 1.1; }
        .stat-card .stat-label { font-size: .8rem; color: #6b7fa3; margin-top: .2rem; }
        .stat-card .stat-bar { height: 3px; margin-top: .75rem; border-radius: 2px; overflow: hidden; background: #e8edf4; }
        .stat-card .stat-bar-fill { height: 100%; border-radius: 2px; }

        /* ── Buttons ── */
        .btn { border-radius: .45rem !important; font-size: .875rem; font-weight: 500; }
        .btn-primary { background: var(--accent) !important; border-color: var(--accent) !important; }
        .btn-primary:hover { background: var(--accent-hover) !important; border-color: var(--accent-hover) !important; }
        .btn-sm { padding: .3rem .7rem !important; font-size: .8rem !important; }

        /* ── Badges ── */
        .badge { font-weight: 500; border-radius: .35rem; letter-spacing: .01em; }

        /* ── Pagination ── */
        .pagination .page-link { border-radius: .4rem !important; margin: 0 2px; color: var(--accent); font-size: .875rem; }
        .pagination .page-item.active .page-link { background: var(--accent); border-color: var(--accent); }

        /* ── Alerts ── */
        .alert { border: none; border-radius: .55rem; font-size: .875rem; }
        .alert-success { background: #e8f8f0; color: #1a6640; }
        .alert-danger { background: #fdecea; color: #7f1d1d; }
        .alert-warning { background: #fef9ec; color: #7c5c10; }

        /* ── Form controls ── */
        .form-control, .form-select, .input-group-text {
            border-color: #dce3ed;
            border-radius: .45rem !important;
            font-size: .875rem;
            color: #2c3e50;
        }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 .2rem rgba(26,133,217,.12); }
        .form-label { font-size: .85rem; font-weight: 500; color: #3d5166; }

        /* ── Upcoming list hover ── */
        .hover-bg:hover { background: #f4f7fc !important; }

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
                top: 0; left: 0;
                height: 100vh;
                z-index: 1045;
                transform: translateX(-100%);
                width: 260px !important;
                box-shadow: 4px 0 24px rgba(0,0,0,.3);
            }
            .sidebar.mobile-open { transform: translateX(0); }
            #app-wrapper { display: block !important; }
            .main-content { width: 100%; }
            .content-padding { padding: .75rem !important; }
            .topbar { padding-left: .75rem !important; padding-right: .75rem !important; }
            .topbar .user-label { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex" id="app-wrapper">

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand d-flex align-items-center gap-2">
            <div class="brand-icon"><i class="bi bi-heart-pulse-fill"></i></div>
            <a href="{{ route('panel.dashboard') }}" class="text-decoration-none d-flex flex-column">
                <span class="brand-text">InnApp</span>
                <span class="brand-sub">{{ auth()->user()->specialty?->name ?? 'İstifadəçi' }}</span>
            </a>
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
        <div class="sub-info-block">
            <div class="sub-pkg sub-info">{{ $subscription->package->name }}</div>
            <div class="sub-row sub-info">
                <span>Müştəri</span>
                <span>{{ $subscription->patients_used }}/{{ $subscription->package->patient_limit ?? '∞' }}</span>
            </div>
            <div class="sub-row sub-info">
                <span>SMS</span>
                <span>{{ $subscription->sms_used }}/{{ $subscription->package->sms_limit ?? '∞' }}</span>
            </div>
            <div class="sub-exp sub-info">Son: {{ $subscription->expires_at->format('d.m.Y') }}</div>
        </div>
        @endif

        <div class="bottom-section">
            <div class="user-name">{{ auth()->user()->full_name }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn btn btn-sm w-100 d-flex align-items-center justify-content-center gap-1">
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
        <div class="topbar px-4 py-2 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button id="sidebar-toggle" title="Menunu gizlət / göstər">
                    <i class="bi bi-list" id="toggle-icon"></i>
                </button>
                <span class="page-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="user-badge">İstifadəçi</span>
                <span class="user-label">{{ auth()->user()->full_name }}</span>
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
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
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
