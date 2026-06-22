<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Promotor Panel') — InnApp</title>
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --body-bg: #f8fafc;
            --sidebar-bg: #0b1220;
            --sidebar-hover-bg: rgba(255,255,255,.04);
            --sidebar-border: rgba(255,255,255,.05);
            --sidebar-text: #94a3b8;
            --accent: #0ea5e9;
            --accent-hover: #0284c7;
            --accent-light: rgba(14,165,233,.08);
            --text-dark: #0f172a;
            --text-mid: #475569;
            --text-light: #64748b;
            --border-color: #f1f5f9;
            --card-shadow: 0 1px 3px 0 rgba(0,0,0,.05), 0 4px 12px -2px rgba(15,23,42,.02);
        }
        * { box-sizing: border-box; font-family: 'Inter', system-ui, sans-serif; }
        body { background: var(--body-bg); color: var(--text-dark); min-height: 100vh; overflow-x: hidden; letter-spacing: -.01em; }
        .app-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: var(--sidebar-bg); border-right: 1px solid var(--sidebar-border); display: flex; flex-direction: column; flex-shrink: 0; position: fixed; top: 0; bottom: 0; left: 0; z-index: 1000; transition: transform .3s; }
        .sidebar-brand { padding: 1.75rem 1.5rem; display: flex; align-items: center; gap: .75rem; border-bottom: 1px solid var(--sidebar-border); }
        .sidebar-brand .brand-icon { width: 38px; height: 38px; background: linear-gradient(135deg, var(--accent), #38bdf8); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .sidebar-brand .brand-icon i { color: #fff; font-size: 1.2rem; }
        .sidebar-brand .brand-text { color: #fff; font-weight: 700; font-size: 1.25rem; letter-spacing: -.02em; }
        .sidebar-brand .brand-sub { color: var(--sidebar-text); font-size: .65rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; margin-top: 2px; }
        .sidebar-nav-container { flex-grow: 1; padding: 1.5rem 1rem; overflow-y: auto; }
        .sidebar .nav { gap: 4px; }
        .sidebar .nav-link { color: var(--sidebar-text); padding: .7rem 1rem; border-radius: 10px; display: flex; align-items: center; font-size: .875rem; font-weight: 500; transition: all .2s; border-left: 3px solid transparent; gap: .75rem; }
        .sidebar .nav-link i { font-size: 1.15rem; color: var(--sidebar-text); }
        .sidebar .nav-link:hover { background: var(--sidebar-hover-bg); color: #fff; transform: translateX(3px); }
        .sidebar .nav-link:hover i { color: #fff; }
        .sidebar .nav-link.active { background: linear-gradient(90deg, rgba(14,165,233,.15) 0%, rgba(14,165,233,.02) 100%) !important; color: #fff !important; font-weight: 600; border-left: 4px solid var(--accent) !important; border-radius: 0 10px 10px 0 !important; }
        .sidebar .nav-link.active i { color: #38bdf8 !important; }
        .sidebar .bottom-section { padding: 1.25rem; border-top: 1px solid var(--sidebar-border); background: rgba(0,0,0,.15); display: flex; flex-direction: column; gap: .75rem; }
        .sidebar .bottom-section .user-label { color: #fff; font-size: .875rem; font-weight: 600; display: flex; align-items: center; gap: .5rem; }
        .sidebar .bottom-section .user-label::before { content: ''; width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 0 4px rgba(16,185,129,.18); }
        .sidebar .logout-btn { background: rgba(239,68,68,.08) !important; border: 1px solid rgba(239,68,68,.15) !important; color: #f87171 !important; font-size: .8rem !important; padding: .55rem .85rem !important; border-radius: 8px !important; }
        .sidebar .logout-btn:hover { background: rgba(239,68,68,.15) !important; color: #fff !important; }
        .main-content { flex: 1; margin-left: 280px; min-width: 0; min-height: 100vh; display: flex; flex-direction: column; transition: margin-left .3s; }
        .topbar { background: rgba(255,255,255,.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(15,23,42,.06); padding: 1.1rem 2rem; position: sticky; top: 0; z-index: 99; display: flex; align-items: center; justify-content: space-between; }
        .topbar .page-title { font-size: 1.15rem; font-weight: 700; color: var(--text-dark); letter-spacing: -.02em; }
        .topbar .role-badge { background: var(--accent-light); color: var(--accent); font-size: .75rem; padding: .3rem .75rem; border-radius: 999px; font-weight: 600; border: 1px solid rgba(14,165,233,.12); }
        .mobile-menu-toggle { display: none; background: none; border: none; font-size: 1.5rem; color: var(--text-dark); cursor: pointer; }
        .content-shell { padding: 2rem; flex-grow: 1; }
        .card { background: #fff; border: 1px solid var(--border-color) !important; box-shadow: var(--card-shadow); border-radius: 14px !important; overflow: hidden; }
        .card-header { background: #fff !important; border-bottom: 1px solid var(--border-color) !important; padding: 1.25rem 1.5rem; font-weight: 600; color: var(--text-dark); font-size: .95rem; }
        .card-body { padding: 1.5rem; }
        .card-footer { background: #fff !important; border-top: 1px solid var(--border-color) !important; padding: 1.1rem 1.5rem; }
        .stat-card { border: 1px solid var(--border-color) !important; background: #fff; border-radius: 14px !important; transition: transform .2s, box-shadow .2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(15,23,42,.05); }
        .stat-card .stat-icon { width: 46px; height: 46px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .stat-card .stat-value { font-size: 1.8rem; font-weight: 700; color: var(--text-dark); line-height: 1.1; letter-spacing: -.03em; }
        .stat-card .stat-label { font-size: .8rem; font-weight: 500; color: var(--text-light); text-transform: uppercase; letter-spacing: .03em; margin-bottom: .15rem; }
        .table { margin-bottom: 0; font-size: .875rem; }
        .table thead th { background: #fafbfd !important; color: var(--text-light); font-weight: 600; font-size: .725rem; text-transform: uppercase; letter-spacing: .05em; padding: .9rem 1.25rem; border-bottom: 1px solid var(--border-color) !important; }
        .table tbody td { padding: 1.1rem 1.25rem; vertical-align: middle; color: var(--text-mid); border-bottom: 1px solid var(--border-color); }
        .table-hover tbody tr:hover { background: #fafbfc !important; }
        .form-control, .form-select { border: 1px solid #e2e8f0; border-radius: 10px !important; font-size: .875rem; color: var(--text-dark); padding: .6rem .9rem; }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(14,165,233,.12); }
        .form-label { font-size: .825rem; font-weight: 500; color: var(--text-mid); margin-bottom: .45rem; }
        .btn { border-radius: 10px !important; font-weight: 500; font-size: .875rem; padding: .55rem 1.1rem; transition: all .2s; }
        .btn-sm { padding: .35rem .8rem !important; font-size: .775rem !important; border-radius: 8px !important; }
        .btn-primary { background: var(--accent) !important; border-color: var(--accent) !important; color: #fff !important; }
        .btn-primary:hover { background: var(--accent-hover) !important; border-color: var(--accent-hover) !important; transform: translateY(-1px); }
        .btn-outline-secondary { color: var(--text-mid) !important; border-color: #e2e8f0 !important; }
        .badge { font-size: .75rem; font-weight: 600; padding: .35rem .75rem; border-radius: 8px; display: inline-flex; align-items: center; border: 1px solid transparent; }
        .badge.bg-success { background: #ecfdf5 !important; color: #065f46 !important; border-color: #d1fae5 !important; }
        .badge.bg-danger { background: #fef2f2 !important; color: #991b1b !important; border-color: #fee2e2 !important; }
        .badge.bg-warning { background: #fffbeb !important; color: #92400e !important; border-color: #fef3c7 !important; }
        .badge.bg-info { background: #f0f9ff !important; color: #075985 !important; border-color: #e0f2fe !important; }
        .badge.bg-secondary { background: #f8fafc !important; color: #475569 !important; border-color: #e2e8f0 !important; }
        .pagination { gap: 2px; margin-bottom: 0; }
        .pagination .page-link { border: 1px solid #e2e8f0 !important; border-radius: 8px !important; color: var(--text-mid); font-size: .825rem; padding: .4rem .75rem; }
        .pagination .page-item.active .page-link { background: var(--accent) !important; border-color: var(--accent) !important; color: #fff !important; }
        .alert { border: 1px solid transparent; border-radius: 12px; padding: .95rem 1.25rem; font-size: .875rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; }
        .alert-success { background: #ecfdf5; border-color: #d1fae5; color: #065f46; }
        .alert-danger { background: #fef2f2; border-color: #fee2e2; color: #991b1b; }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-menu-toggle { display: block; }
            .topbar { padding: 1rem 1.25rem; }
            .content-shell { padding: 1.25rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-wrapper">
    <nav class="sidebar" id="app-sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="bi bi-megaphone-fill"></i></div>
            <a href="{{ route('promoter.dashboard') }}" class="text-decoration-none d-flex flex-column">
                <span class="brand-text">InnApp</span>
                <span class="brand-sub">Promotor</span>
            </a>
        </div>
        <div class="sidebar-nav-container">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('promoter.dashboard') }}" class="nav-link {{ request()->routeIs('promoter.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('promoter.codes') }}" class="nav-link {{ request()->routeIs('promoter.codes') ? 'active' : '' }}">
                        <i class="bi bi-ticket-perforated"></i>Promo Kodlarım
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('promoter.redemptions') }}" class="nav-link {{ request()->routeIs('promoter.redemptions') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>Müştərilər / Komissiya
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('promoter.payouts.index') }}" class="nav-link {{ request()->routeIs('promoter.payouts*') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i>Balans / Çıxarış
                    </a>
                </li>
            </ul>
        </div>
        <div class="bottom-section">
            <div class="user-label">{{ auth()->user()->full_name }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn btn btn-sm w-100 d-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-box-arrow-right flex-shrink-0"></i>Çıxış
                </button>
            </form>
        </div>
    </nav>

    <div class="main-content">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="mobile-menu-toggle" id="menu-toggle-btn" aria-label="Menu"><i class="bi bi-list"></i></button>
                <span class="page-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="role-badge">Promotor</span>
                <span class="fw-semibold text-dark" style="font-size:.875rem">{{ auth()->user()->full_name }}</span>
            </div>
        </header>

        <main class="content-shell">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <span><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <span><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('menu-toggle-btn');
    const sidebar = document.getElementById('app-sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function (e) { e.stopPropagation(); sidebar.classList.toggle('mobile-open'); });
        document.addEventListener('click', function (e) {
            if (sidebar.classList.contains('mobile-open') && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                sidebar.classList.remove('mobile-open');
            }
        });
    }
});
</script>
@stack('scripts')
</body>
</html>
