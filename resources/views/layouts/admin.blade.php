<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') — InnApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { width: 260px; min-height: 100vh; background: #1e293b; }
        .sidebar .nav-link { color: #94a3b8; padding: .6rem 1.25rem; border-radius: .375rem; margin: 2px 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #334155; color: #fff; }
        .sidebar .nav-link i { width: 20px; }
        .sidebar-brand { padding: 1.25rem; border-bottom: 1px solid #334155; }
        .main-content { flex: 1; overflow-x: hidden; }
        .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar d-flex flex-column">
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}" class="text-white text-decoration-none fw-bold fs-5">
                <i class="bi bi-grid-fill me-2"></i>InnApp
            </a>
            <div class="text-secondary small mt-1">Super Admin Panel</div>
        </div>
        <ul class="nav flex-column mt-3 flex-grow-1">
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
        <div class="p-3 border-top border-secondary">
            <div class="text-secondary small mb-2">{{ auth()->user()->name }} {{ auth()->user()->surname }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-box-arrow-right me-1"></i>Çıxış
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar px-4 py-3 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-semibold">@yield('page-title', 'Dashboard')</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger">Super Admin</span>
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
