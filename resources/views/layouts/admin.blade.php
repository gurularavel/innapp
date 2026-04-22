<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') — InnApp</title>
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon/favicon-96x96.png">
    <link rel="apple-touch-icon" href="/favicon/apple-touch-icon.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sb-bg: #15202e;
            --sb-brand-bg: #0f1820;
            --sb-active: #e84393;
            --sb-active-bg: rgba(232,67,147,.13);
            --sb-hover-bg: rgba(255,255,255,.05);
            --sb-text: #8a9bb0;
            --sb-text-active: #ffffff;
            --sb-border: rgba(255,255,255,.07);
            --accent: #e84393;
            --body-bg: #eef2f7;
            --topbar-shadow: 0 1px 0 #e2e9f3, 0 2px 8px rgba(15,24,32,.06);
            --card-shadow: 0 2px 12px rgba(15,24,32,.07);
        }
        * { box-sizing: border-box; }
        body { background-color: var(--body-bg); font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--sb-bg);
            display: flex; flex-direction: column;
            flex-shrink: 0;
            box-shadow: 2px 0 12px rgba(0,0,0,.2);
        }
        .sidebar-brand {
            background: var(--sb-brand-bg);
            padding: 1.2rem 1.25rem;
            border-bottom: 1px solid var(--sb-border);
        }
        .sidebar-brand .brand-icon {
            width: 34px; height: 34px;
            background: var(--accent);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; color: #fff;
            box-shadow: 0 2px 8px rgba(232,67,147,.4);
            flex-shrink: 0;
        }
        .sidebar-brand .brand-text { color: #fff; font-weight: 700; font-size: 1.05rem; }
        .sidebar-brand .brand-sub { color: var(--sb-text); font-size: .72rem; margin-top: 1px; }
        .sidebar ul.nav { padding: .75rem 0; }
        .sidebar .nav-link {
            color: var(--sb-text);
            padding: .55rem 1rem;
            margin: 1px .75rem;
            border-radius: .45rem;
            display: flex; align-items: center;
            font-size: .875rem; font-weight: 500;
            transition: background .15s, color .15s;
            border-left: 2px solid transparent;
        }
        .sidebar .nav-link i { width: 20px; font-size: 1rem; flex-shrink: 0; color: var(--sb-text); transition: color .15s; }
        .sidebar .nav-link:hover { background: var(--sb-hover-bg); color: #fff; }
        .sidebar .nav-link:hover i { color: #fff; }
        .sidebar .nav-link.active { background: var(--sb-active-bg); color: var(--sb-text-active); border-left-color: var(--sb-active); }
        .sidebar .nav-link.active i { color: var(--sb-active); }
        .sidebar .bottom-section { padding: .85rem 1rem; border-top: 1px solid var(--sb-border); margin-top: auto; }
        .sidebar .bottom-section .user-label { color: var(--sb-text); font-size: .8rem; font-weight: 500; margin-bottom: .5rem; display: flex; align-items: center; gap: .5rem; }
        .sidebar .bottom-section .user-label::before { content: ''; width: 7px; height: 7px; background: #e84393; border-radius: 50%; flex-shrink: 0; }
        .sidebar .logout-btn {
            background: rgba(255,255,255,.07) !important;
            border: 1px solid rgba(255,255,255,.12) !important;
            color: var(--sb-text) !important;
            font-size: .82rem !important;
            padding: .38rem .75rem !important;
            border-radius: .45rem !important;
        }
        .sidebar .logout-btn:hover { background: rgba(255,255,255,.13) !important; color: #fff !important; }
        .main-content { flex: 1; overflow-x: hidden; }
        .topbar {
            background: #fff;
            box-shadow: var(--topbar-shadow);
            position: sticky; top: 0; z-index: 100;
        }
        .topbar .page-title { font-size: 1rem; font-weight: 600; color: #1a2e4a; }
        .topbar .admin-badge { background: var(--accent); color: #fff; font-size: .72rem; padding: .22rem .65rem; border-radius: 20px; font-weight: 600; }
        .topbar .user-label { color: #4a6080; font-size: .83rem; font-weight: 500; }
        .card { border: none !important; box-shadow: var(--card-shadow); border-radius: .65rem !important; }
        .card-header { background: #fff !important; border-bottom: 1px solid #e8edf4 !important; font-weight: 600; color: #1a2e4a; padding: .85rem 1.25rem; }
        .card-footer { background: #fff !important; }
        .table { font-size: .875rem; }
        .table thead th { background: #f4f7fc; color: #4a6080; font-weight: 600; font-size: .78rem; text-transform: uppercase; letter-spacing: .05em; border-bottom: none; padding: .7rem 1rem; }
        .table tbody td { padding: .75rem 1rem; vertical-align: middle; color: #2c3e50; }
        .table-hover tbody tr:hover { background-color: #f8fafd !important; }
        .btn { border-radius: .45rem !important; font-weight: 500; font-size: .875rem; }
        .btn-sm { padding: .3rem .7rem !important; font-size: .8rem !important; }
        .badge { font-weight: 500; border-radius: .35rem; }
        .pagination .page-link { border-radius: .4rem !important; margin: 0 2px; color: var(--accent); font-size: .875rem; }
        .pagination .page-item.active .page-link { background: var(--accent); border-color: var(--accent); }
        .form-control, .form-select, .input-group-text { border-color: #dce3ed; border-radius: .45rem !important; font-size: .875rem; color: #2c3e50; }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 .2rem rgba(232,67,147,.12); }
        .form-label { font-size: .85rem; font-weight: 500; color: #3d5166; }
        .stat-card { border-radius: .65rem !important; border: none !important; box-shadow: var(--card-shadow); overflow: hidden; }
        .stat-card .stat-icon { width: 48px; height: 48px; border-radius: .5rem; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; color: #1a2e4a; line-height: 1.1; }
        .stat-card .stat-label { font-size: .8rem; color: #6b7fa3; margin-top: .2rem; }
        .alert { border: none; border-radius: .55rem; font-size: .875rem; }
        .alert-success { background: #e8f8f0; color: #1a6640; }
        .alert-danger { background: #fdecea; color: #7f1d1d; }
        .alert-warning { background: #fef9ec; color: #7c5c10; }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand d-flex align-items-center gap-2">
            <div class="brand-icon"><i class="bi bi-shield-fill"></i></div>
            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none d-flex flex-column">
                <span class="brand-text">InnApp</span>
                <span class="brand-sub">Super Admin Panel</span>
            </a>
        </div>
        <ul class="nav flex-column flex-grow-1">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.doctors.index') }}" class="nav-link {{ request()->routeIs('admin.doctors*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge me-2"></i>İstifadəçilər
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.specialties.index') }}" class="nav-link {{ request()->routeIs('admin.specialties*') ? 'active' : '' }}">
                    <i class="bi bi-bookmark me-2"></i>İxtisaslar
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.packages.index') }}" class="nav-link {{ request()->routeIs('admin.packages*') ? 'active' : '' }}">
                    <i class="bi bi-box me-2"></i>Paketlər
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.subscriptions.index') }}" class="nav-link {{ request()->routeIs('admin.subscriptions*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card me-2"></i>Abunəliklər
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
                    <i class="bi bi-receipt me-2"></i>Ödənişlər
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.sms-logs.index') }}" class="nav-link {{ request()->routeIs('admin.sms-logs*') ? 'active' : '' }}">
                    <i class="bi bi-chat-dots me-2"></i>SMS Loqlar
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.settings.sms-templates') }}" class="nav-link {{ request()->routeIs('admin.settings.sms*') ? 'active' : '' }}">
                    <i class="bi bi-pencil-square me-2"></i>Defolt SMS Şablonları
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.settings.smtp') }}" class="nav-link {{ request()->routeIs('admin.settings.smtp*') ? 'active' : '' }}">
                    <i class="bi bi-envelope-at me-2"></i>SMTP / E-poçt
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.cron-log') }}" class="nav-link {{ request()->routeIs('admin.cron-log') ? 'active' : '' }}">
                    <i class="bi bi-terminal me-2"></i>Cron / SMS Test
                </a>
            </li>
        </ul>
        <div class="bottom-section">
            <div class="user-label">{{ auth()->user()->name }} {{ auth()->user()->surname }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn btn btn-sm w-100 d-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-box-arrow-right flex-shrink-0"></i>Çıxış
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar px-4 py-2 d-flex align-items-center justify-content-between">
            <span class="page-title">@yield('page-title', 'Dashboard')</span>
            <div class="d-flex align-items-center gap-2">
                <span class="admin-badge">Super Admin</span>
                <span class="user-label">{{ auth()->user()->full_name }}</span>
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
<script src="https://cdn.jsdelivr.net/npm/imask@7.6.1/dist/imask.min.js"></script>
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
@stack('scripts')
</body>
</html>
