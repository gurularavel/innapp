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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Palette variables */
            --body-bg: #f8fafc;
            --sidebar-bg: #090e1a;
            --sidebar-hover-bg: rgba(255, 255, 255, 0.04);
            --sidebar-active-bg: rgba(99, 102, 241, 0.12);
            --sidebar-border: rgba(255, 255, 255, 0.05);
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #ffffff;
            
            --accent-primary: #4f46e5; /* Indigo */
            --accent-primary-hover: #4338ca;
            --accent-primary-light: rgba(79, 70, 229, 0.08);
            
            --text-dark: #0f172a; /* Slate 900 */
            --text-mid: #475569; /* Slate 600 */
            --text-light: #64748b; /* Slate 500 */
            --border-color: #f1f5f9; /* Slate 100 */
            
            /* Shadows */
            --card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05), 0 4px 12px -2px rgba(15, 23, 42, 0.02);
            --topbar-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
            --glow-shadow: 0 0 15px rgba(99, 102, 241, 0.15);
        }

        * {
            box-sizing: border-box;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--body-bg);
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
            letter-spacing: -0.01em;
        }

        /* App Layout Container */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 280px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Logo Brand section */
        .sidebar-brand {
            padding: 1.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--sidebar-border);
        }

        .sidebar-brand .brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--accent-primary), #818cf8);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--glow-shadow);
        }

        .sidebar-brand .brand-icon img {
            width: 20px;
            height: 20px;
        }

        .sidebar-brand .brand-text {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: -0.02em;
        }

        .sidebar-brand .brand-sub {
            color: var(--sidebar-text);
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 2px;
        }

        /* Navigation menu */
        .sidebar-nav-container {
            flex-grow: 1;
            padding: 1.5rem 1rem;
            overflow-y: auto;
        }

        /* Yalnız Firefox: standart scrollbar rəngi (Chrome-da webkit stilləri işləyir) */
        @supports (-moz-appearance: none) {
            .sidebar-nav-container {
                scrollbar-width: thin;
                scrollbar-color: rgba(255, 255, 255, 0.85) transparent;
            }
        }

        .sidebar-nav-container::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 4px;
        }

        .sidebar-nav-container::-webkit-scrollbar-thumb:hover {
            background: #ffffff;
        }

        .sidebar .nav {
            gap: 4px;
        }

        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 0.7rem 1rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            gap: 0.75rem;
        }

        .sidebar .nav-link i {
            font-size: 1.15rem;
            transition: transform 0.2s ease, color 0.2s ease;
            color: var(--sidebar-text);
        }

        .sidebar .nav-link:hover {
            background-color: var(--sidebar-hover-bg);
            color: var(--sidebar-text-active);
            transform: translateX(3px);
        }

        .sidebar .nav-link:hover i {
            color: #ffffff;
            transform: scale(1.05);
        }

        .sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(79, 70, 229, 0.15) 0%, rgba(79, 70, 229, 0.02) 100%) !important;
            color: #ffffff !important;
            font-weight: 600;
            border-left: 4px solid #6366f1 !important;
            border-radius: 0 10px 10px 0 !important;
        }

        .sidebar .nav-link.active i {
            color: #818cf8 !important;
        }

        /* User profile bottom bar */
        .sidebar .bottom-section {
            padding: 1.25rem 1.25rem;
            border-top: 1px solid var(--sidebar-border);
            background-color: rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .sidebar .bottom-section .user-label {
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar .bottom-section .user-label::before {
            content: '';
            width: 8px;
            height: 8px;
            background-color: #10b981; /* active indicator green */
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.18);
        }

        .sidebar .logout-btn {
            background: rgba(239, 68, 68, 0.08) !important;
            border: 1px solid rgba(239, 68, 68, 0.15) !important;
            color: #f87171 !important;
            font-size: 0.8rem !important;
            padding: 0.55rem 0.85rem !important;
            border-radius: 8px !important;
            transition: all 0.2s ease !important;
        }

        .sidebar .logout-btn:hover {
            background: rgba(239, 68, 68, 0.15) !important;
            color: #ffffff !important;
        }

        /* Main Content container */
        .main-content {
            flex: 1;
            margin-left: 280px;
            min-width: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Premium Topbar */
        .topbar {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
            padding: 1.1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 99;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar .page-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.02em;
        }

        .topbar .admin-badge {
            background-color: var(--accent-primary-light);
            color: var(--accent-primary);
            font-size: 0.75rem;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            font-weight: 600;
            border: 1px solid rgba(79, 70, 229, 0.12);
        }

        .topbar .user-label {
            color: var(--text-mid);
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Mobile Hamburger & Controls */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-dark);
            cursor: pointer;
            padding: 0.25rem;
        }

        /* Shell content area */
        .content-shell {
            padding: 2rem;
            flex-grow: 1;
        }

        /* Cards Restyling */
        .card {
            background: #ffffff;
            border: 1px solid var(--border-color) !important;
            box-shadow: var(--card-shadow);
            border-radius: 14px !important;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid var(--border-color) !important;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-footer {
            background-color: #ffffff !important;
            border-top: 1px solid var(--border-color) !important;
            padding: 1.1rem 1.5rem;
        }

        /* Stats Cards */
        .stat-card {
            border-top: none !important;
            border: 1px solid var(--border-color) !important;
            background: #ffffff;
            border-radius: 14px !important;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.05);
        }

        .stat-card .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .stat-card .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.1;
            letter-spacing: -0.03em;
        }

        .stat-card .stat-label {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 0.15rem;
        }

        /* Tables Restyling */
        .table {
            margin-bottom: 0;
            font-size: 0.875rem;
        }

        .table thead th {
            background-color: #fafbfd !important;
            color: var(--text-light);
            font-weight: 600;
            font-size: 0.725rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.9rem 1.25rem;
            border-bottom: 1px solid var(--border-color) !important;
        }

        .table tbody td {
            padding: 1.1rem 1.25rem;
            vertical-align: middle;
            color: var(--text-mid);
            border-bottom: 1px solid var(--border-color);
        }

        .table-hover tbody tr:hover {
            background-color: #fafbfc !important;
        }

        .table-hover tbody tr {
            transition: background-color 0.15s ease;
        }

        /* Form Controls */
        .form-control, .form-select, .input-group-text {
            border: 1px solid #e2e8f0;
            border-radius: 10px !important;
            font-size: 0.875rem;
            color: var(--text-dark);
            padding: 0.6rem 0.9rem;
            transition: all 0.2s ease;
            background-color: #ffffff;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
            color: var(--text-dark);
        }

        .form-label {
            font-size: 0.825rem;
            font-weight: 500;
            color: var(--text-mid);
            margin-bottom: 0.45rem;
        }

        /* Buttons Restyling */
        .btn {
            border-radius: 10px !important;
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.55rem 1.1rem;
            transition: all 0.2s ease;
        }

        .btn-sm {
            padding: 0.35rem 0.8rem !important;
            font-size: 0.775rem !important;
            border-radius: 8px !important;
        }

        .btn-primary {
            background-color: var(--accent-primary) !important;
            border-color: var(--accent-primary) !important;
            color: #ffffff !important;
        }

        .btn-primary:hover {
            background-color: var(--accent-primary-hover) !important;
            border-color: var(--accent-primary-hover) !important;
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            color: var(--accent-primary) !important;
            border-color: var(--accent-primary) !important;
            background-color: transparent !important;
        }

        .btn-outline-primary:hover {
            background-color: var(--accent-primary) !important;
            color: #ffffff !important;
        }

        /* Beautiful Soft Badges */
        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            border: 1px solid transparent;
        }

        .badge.bg-success {
            background-color: #ecfdf5 !important;
            color: #065f46 !important;
            border-color: #d1fae5 !important;
        }

        .badge.bg-danger {
            background-color: #fef2f2 !important;
            color: #991b1b !important;
            border-color: #fee2e2 !important;
        }

        .badge.bg-warning {
            background-color: #fffbeb !important;
            color: #92400e !important;
            border-color: #fef3c7 !important;
        }

        .badge.bg-secondary {
            background-color: #f8fafc !important;
            color: #475569 !important;
            border-color: #e2e8f0 !important;
        }

        .badge.bg-info {
            background-color: #f0f9ff !important;
            color: #075985 !important;
            border-color: #e0f2fe !important;
        }

        /* Pagination style rules */
        .pagination {
            gap: 2px;
            margin-bottom: 0;
        }

        .pagination .page-link {
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            color: var(--text-mid);
            font-size: 0.825rem;
            padding: 0.4rem 0.75rem;
            transition: all 0.2s ease;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--accent-primary) !important;
            border-color: var(--accent-primary) !important;
            color: #ffffff !important;
        }

        .pagination .page-link:hover {
            background-color: #f8fafc;
            color: var(--accent-primary);
        }

        /* Custom alert components */
        .alert {
            border: 1px solid transparent;
            border-radius: 12px;
            padding: 0.95rem 1.25rem;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .alert-success {
            background-color: #ecfdf5;
            border-color: #d1fae5;
            color: #065f46;
        }

        .alert-danger {
            background-color: #fef2f2;
            border-color: #fee2e2;
            color: #991b1b;
        }

        .alert-warning {
            background-color: #fffbeb;
            border-color: #fef3c7;
            color: #92400e;
        }

        /* Responsive styling */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .topbar {
                padding: 1rem 1.25rem;
            }

            .content-shell {
                padding: 1.25rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-wrapper">
    <!-- Sidebar -->
    <nav class="sidebar" id="app-sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <img src="{{ asset('favicon/favicon.svg') }}" alt="InnApp">
            </div>
            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none d-flex flex-column">
                <span class="brand-text">InnApp</span>
                <span class="brand-sub">Super Admin</span>
            </a>
        </div>
        <div class="sidebar-nav-container">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>İstifadəçilər
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.admins.index') }}" class="nav-link {{ request()->routeIs('admin.admins*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock"></i>Adminlər
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.specialties.index') }}" class="nav-link {{ request()->routeIs('admin.specialties*') ? 'active' : '' }}">
                        <i class="bi bi-bookmarks"></i>İxtisaslar
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.packages.index') }}" class="nav-link {{ request()->routeIs('admin.packages*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i>Paketlər
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.subscriptions.index') }}" class="nav-link {{ request()->routeIs('admin.subscriptions*') ? 'active' : '' }}">
                        <i class="bi bi-credit-card"></i>Abunəliklər
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
                        <i class="bi bi-wallet2"></i>Ödənişlər
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.promoters.index') }}" class="nav-link {{ request()->routeIs('admin.promoters*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i>Promotorlar
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.promo-codes.index') }}" class="nav-link {{ request()->routeIs('admin.promo-codes*') ? 'active' : '' }}">
                        <i class="bi bi-ticket-perforated"></i>Promo Kodlar
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.payouts.index') }}" class="nav-link {{ request()->routeIs('admin.payouts*') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i>Çıxarışlar
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.sms-logs.index') }}" class="nav-link {{ request()->routeIs('admin.sms-logs*') ? 'active' : '' }}">
                        <i class="bi bi-chat-text"></i>SMS Loqlar
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings.sms-templates') }}" class="nav-link {{ request()->routeIs('admin.settings.sms*') ? 'active' : '' }}">
                        <i class="bi bi-chat-quote"></i>SMS Şablonları
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings.smtp') }}" class="nav-link {{ request()->routeIs('admin.settings.smtp*') ? 'active' : '' }}">
                        <i class="bi bi-envelope"></i>SMTP / E-poçt
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings.terms') }}" class="nav-link {{ request()->routeIs('admin.settings.terms*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>İstifadə Qaydaları
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings.promo') }}" class="nav-link {{ request()->routeIs('admin.settings.promo*') ? 'active' : '' }}">
                        <i class="bi bi-percent"></i>Promotor Ayarları
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.cron-log') }}" class="nav-link {{ request()->routeIs('admin.cron-log') ? 'active' : '' }}">
                        <i class="bi bi-terminal-fill"></i>Cron / SMS Test
                    </a>
                </li>
            </ul>
        </div>
        <div class="bottom-section">
            <a href="{{ route('admin.profile.edit') }}" class="user-label text-decoration-none {{ request()->routeIs('admin.profile*') ? 'text-white' : '' }}">
                {{ auth()->user()->name }} {{ auth()->user()->surname }}
            </a>
            <a href="{{ route('admin.profile.edit') }}" class="btn btn-sm w-100 d-flex align-items-center justify-content-center gap-1"
               style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#cbd5e1">
                <i class="bi bi-person-gear flex-shrink-0"></i>Profil
            </a>
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
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="mobile-menu-toggle" id="menu-toggle-btn" aria-label="Menu">
                    <i class="bi bi-list"></i>
                </button>
                <span class="page-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="admin-badge">Super Admin</span>
                <a href="{{ route('admin.profile.edit') }}" class="user-label fw-semibold text-dark text-decoration-none d-inline-flex align-items-center gap-1">
                    <i class="bi bi-person-circle"></i>{{ auth()->user()->full_name }}
                </a>
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
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    <span><i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('warning') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/imask@7.6.1/dist/imask.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Mobile menu toggle functionality
    const toggleBtn = document.getElementById('menu-toggle-btn');
    const sidebar = document.getElementById('app-sidebar');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            sidebar.classList.toggle('mobile-open');
        });

        document.addEventListener('click', function (e) {
            if (sidebar.classList.contains('mobile-open') && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                sidebar.classList.remove('mobile-open');
            }
        });
    }

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
