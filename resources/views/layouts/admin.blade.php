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
            --sb-bg: #071628;
            --sb-brand-bg: rgba(255,255,255,.04);
            --sb-active: #1bc8c8;
            --sb-active-bg: rgba(27,200,200,.14);
            --sb-hover-bg: rgba(255,255,255,.06);
            --sb-text: rgba(255,255,255,.64);
            --sb-text-active: #ffffff;
            --sb-border: rgba(255,255,255,.09);
            --accent: #0e86d4;
            --accent-2: #1bc8c8;
            --accent-soft: rgba(14,134,212,.12);
            --body-bg: #eef5fb;
            --topbar-shadow: 0 10px 40px rgba(14,30,53,.07);
            --card-shadow: 0 18px 40px rgba(14,30,53,.08);
            --card-border: #e4edf6;
            --text-dark: #0e1e35;
            --text-mid: #4f6480;
        }
        * { box-sizing: border-box; }
        body { background: radial-gradient(circle at top left, #f7fbff 0%, var(--body-bg) 55%); font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; color: var(--text-dark); }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, #061526 0%, #0c2440 100%);
            display: flex; flex-direction: column;
            flex-shrink: 0;
            box-shadow: 18px 0 42px rgba(6, 21, 38, .22);
            position: sticky;
            top: 0;
        }
        .sidebar-brand {
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
            padding: 1.1rem 1.15rem;
            border-bottom: 1px solid var(--sb-border);
            margin: 14px 14px 0;
            border-radius: 18px;
        }
        .sidebar-brand .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 16px 30px rgba(14,134,212,.26);
            flex-shrink: 0;
        }
        .sidebar-brand .brand-icon img {
            width: 24px;
            height: 24px;
        }
        .sidebar-brand .brand-text { color: #fff; font-weight: 800; font-size: 1.12rem; letter-spacing: .02em; }
        .sidebar-brand .brand-sub { color: rgba(255,255,255,.5); font-size: .7rem; margin-top: 4px; text-transform: uppercase; letter-spacing: .14em; }
        .sidebar ul.nav { padding: 1rem 0; }
        .sidebar .nav-link {
            color: var(--sb-text);
            padding: .8rem 1rem;
            margin: 3px .9rem;
            border-radius: .85rem;
            display: flex; align-items: center;
            font-size: .9rem; font-weight: 600;
            transition: background .18s, color .18s, transform .18s;
            border-left: 0;
        }
        .sidebar .nav-link i { width: 22px; font-size: 1rem; flex-shrink: 0; color: var(--sb-text); transition: color .15s; }
        .sidebar .nav-link:hover { background: var(--sb-hover-bg); color: #fff; transform: translateX(2px); }
        .sidebar .nav-link:hover i { color: #fff; }
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, rgba(14,134,212,.2), rgba(27,200,200,.16));
            color: var(--sb-text-active);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.05);
        }
        .sidebar .nav-link.active i { color: var(--sb-active); }
        .sidebar .bottom-section {
            padding: 1rem;
            border-top: 1px solid var(--sb-border);
            margin-top: auto;
            background: rgba(0,0,0,.08);
        }
        .sidebar .bottom-section .user-label { color: var(--sb-text); font-size: .82rem; font-weight: 600; margin-bottom: .7rem; display: flex; align-items: center; gap: .5rem; }
        .sidebar .bottom-section .user-label::before { content: ''; width: 8px; height: 8px; background: var(--accent-2); border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 0 5px rgba(27,200,200,.12); }
        .sidebar .logout-btn {
            background: rgba(255,255,255,.08) !important;
            border: 1px solid rgba(255,255,255,.12) !important;
            color: #ffffff !important;
            font-size: .84rem !important;
            padding: .6rem .85rem !important;
            border-radius: .8rem !important;
        }
        .sidebar .logout-btn:hover { background: rgba(255,255,255,.13) !important; color: #fff !important; }
        .main-content { flex: 1; overflow-x: hidden; }
        .topbar {
            background: rgba(255,255,255,.84);
            backdrop-filter: blur(12px);
            box-shadow: var(--topbar-shadow);
            position: sticky; top: 0; z-index: 100;
            border-bottom: 1px solid rgba(14,30,53,.05);
        }
        .topbar .page-title { font-size: 1.02rem; font-weight: 700; color: var(--text-dark); }
        .topbar .admin-badge {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #fff;
            font-size: .72rem;
            padding: .34rem .78rem;
            border-radius: 999px;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(14,134,212,.18);
        }
        .topbar .user-label { color: var(--text-mid); font-size: .84rem; font-weight: 600; }
        .card { border: 1px solid var(--card-border) !important; box-shadow: var(--card-shadow); border-radius: 1.1rem !important; }
        .card-header { background: #fff !important; border-bottom: 1px solid #e8edf4 !important; font-weight: 700; color: var(--text-dark); padding: 1rem 1.25rem; }
        .card-footer { background: #fff !important; }
        .table { font-size: .875rem; }
        .table thead th { background: #f4f8fc; color: #4a6080; font-weight: 700; font-size: .78rem; text-transform: uppercase; letter-spacing: .05em; border-bottom: none; padding: .82rem 1rem; }
        .table tbody td { padding: .85rem 1rem; vertical-align: middle; color: #2c3e50; }
        .table-hover tbody tr:hover { background-color: #f8fafd !important; }
        .btn { border-radius: .8rem !important; font-weight: 600; font-size: .875rem; }
        .btn-sm { padding: .3rem .7rem !important; font-size: .8rem !important; }
        .badge { font-weight: 500; border-radius: .35rem; }
        .pagination .page-link { border-radius: .4rem !important; margin: 0 2px; color: var(--accent); font-size: .875rem; }
        .pagination .page-item.active .page-link { background: var(--accent); border-color: var(--accent); }
        .form-control, .form-select, .input-group-text { border-color: #dce3ed; border-radius: .8rem !important; font-size: .875rem; color: #2c3e50; }
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
        .content-shell {
            padding: 1.5rem;
        }
        @media (max-width: 991.98px) {
            .sidebar {
                width: 100%;
                min-height: auto;
                position: relative;
            }
            .d-flex {
                flex-direction: column;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand d-flex align-items-center gap-2">
            <div class="brand-icon">
                <img src="{{ asset('favicon/favicon.svg') }}" alt="InnApp">
            </div>
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

        <div class="content-shell">
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
