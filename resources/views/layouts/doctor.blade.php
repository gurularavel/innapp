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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Palette variables */
            --body-bg: #f8fafc;
            --sidebar-bg: #070d1e;
            --sidebar-hover-bg: rgba(255, 255, 255, 0.04);
            --sidebar-active-bg: rgba(99, 102, 241, 0.12);
            --sidebar-border: rgba(255, 255, 255, 0.05);
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #ffffff;
            
            --accent-primary: #3b82f6; /* Premium Blue */
            --accent-primary-hover: #2563eb;
            --accent-primary-light: rgba(59, 130, 246, 0.08);
            
            --text-dark: #0f172a; /* Slate 900 */
            --text-mid: #475569; /* Slate 600 */
            --text-light: #64748b; /* Slate 500 */
            --border-color: #f1f5f9; /* Slate 100 */
            
            /* Shadows */
            --card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05), 0 4px 12px -2px rgba(15, 23, 42, 0.02);
            --topbar-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
            --glow-shadow: 0 0 15px rgba(59, 130, 246, 0.15);
        }

        /* Tom Select overrides */
        .ts-wrapper.single.dropdown-active .ts-control > .item { display: none !important; }
        .ts-control {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            padding: 0.6rem 0.9rem !important;
            font-size: 0.875rem !important;
            box-shadow: none !important;
        }
        .ts-wrapper.focus .ts-control {
            border-color: var(--accent-primary) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12) !important;
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
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.15);
        }

        /* Logo Brand section */
        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--sidebar-border);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--accent-primary), #60a5fa);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--glow-shadow);
            color: #ffffff;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-text {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.2rem;
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
            padding: 1.25rem 0.75rem;
            overflow-y: auto;
        }

        .sidebar-nav-container::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .sidebar .nav {
            gap: 4px;
        }

        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 0.65rem 0.95rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            gap: 0.75rem;
            white-space: nowrap;
        }

        .sidebar .nav-link i {
            font-size: 1.15rem;
            transition: transform 0.2s ease, color 0.2s ease;
            color: var(--sidebar-text);
            width: 20px;
            text-align: center;
        }

        .sidebar .nav-link:hover {
            background-color: var(--sidebar-hover-bg);
            color: var(--sidebar-text-active);
        }

        .sidebar .nav-link:hover i {
            color: #ffffff;
        }

        .sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0.02) 100%) !important;
            color: #ffffff !important;
            font-weight: 600;
            border-left: 4px solid var(--accent-primary) !important;
            border-radius: 0 10px 10px 0 !important;
        }

        .sidebar .nav-link.active i {
            color: #60a5fa !important;
        }

        /* Subscription info block style (glassmorphism widget) */
        .sub-info-block {
            margin: 0 0.75rem 0.75rem;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 0.85rem 1.1rem;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .sub-info-block .sub-pkg {
            color: #ffffff;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }

        .sub-info-block .sub-row {
            color: var(--sidebar-text);
            font-size: 0.775rem;
            display: flex;
            justify-content: space-between;
            margin-top: 0.2rem;
        }

        .sub-info-block .sub-exp {
            color: #fbbf24; /* Amber yellow */
            font-size: 0.75rem;
            font-weight: 500;
            margin-top: 0.4rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* User profile bottom bar */
        .sidebar .bottom-section {
            padding: 1.25rem 1.1rem;
            border-top: 1px solid var(--sidebar-border);
            background-color: rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .sidebar .bottom-section .user-name {
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .sidebar .bottom-section .user-name::before {
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

        /* ── Collapsed state (Desktop toggle behavior) ── */
        .sidebar-collapsed .sidebar {
            width: 70px;
        }
        
        .sidebar-collapsed .sidebar .nav-link span,
        .sidebar-collapsed .sidebar .sidebar-brand .brand-text,
        .sidebar-collapsed .sidebar .sidebar-brand .brand-sub,
        .sidebar-collapsed .sidebar .sub-info-block,
        .sidebar-collapsed .sidebar .bottom-section .user-name {
            display: none !important;
        }

        .sidebar-collapsed .sidebar .sidebar-brand {
            justify-content: center;
            padding: 1.25rem 0.5rem;
        }

        .sidebar-collapsed .sidebar .nav-link {
            justify-content: center;
            padding: 0.65rem 0;
            margin: 1px 6px;
            border-left-color: transparent !important;
        }

        .sidebar-collapsed .sidebar .nav-link i {
            width: auto;
        }

        .sidebar-collapsed .sidebar .logout-btn {
            padding: 0.5rem !important;
            justify-content: center;
        }
        
        .sidebar-collapsed .sidebar .logout-btn span {
            display: none !important;
        }

        /* ── Main content container ── */
        .main-content {
            flex: 1;
            margin-left: 280px;
            min-width: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-collapsed .main-content {
            margin-left: 70px;
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

        .topbar .user-badge {
            background-color: var(--accent-primary-light);
            color: var(--accent-primary);
            font-size: 0.75rem;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            font-weight: 600;
            border: 1px solid rgba(59, 130, 246, 0.12);
        }

        .topbar .user-label {
            color: var(--text-mid);
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Desktop Sidebar Toggle */
        #sidebar-toggle {
            background: none;
            border: none;
            color: var(--text-light);
            padding: 0.35rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.4rem;
            line-height: 1;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #sidebar-toggle:hover {
            background-color: #f1f5f9;
            color: var(--accent-primary);
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

        .stat-card .stat-bar {
            height: 4px;
            margin-top: 0.85rem;
            border-radius: 999px;
            background: #f1f5f9;
            overflow: hidden;
        }

        .stat-card .stat-bar-fill {
            height: 100%;
            border-radius: 999px;
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
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
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

        /* Soft Badges */
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

        /* Demo mode Banner styling */
        .demo-banner {
            background: linear-gradient(90deg, #f59e0b, #e11d48);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .demo-banner .demo-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
        }

        .demo-banner .demo-cta-btn {
            background-color: #ffffff;
            color: #e11d48;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 0.4rem 1rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .demo-banner .demo-cta-btn:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Sidebar backdrop overlay on mobile */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 999;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            transition: opacity 0.25s ease;
        }
        .sidebar-backdrop.show {
            display: block;
        }

        /* ── Mobile responsiveness prioritizations ── */
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1001;
                transform: translateX(-100%);
                width: 280px !important;
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content, .sidebar-collapsed .main-content {
                margin-left: 0 !important;
            }

            .topbar {
                padding: 1rem 1.25rem;
            }

            .topbar .user-label {
                display: none;
            }

            .content-shell {
                padding: 1.25rem;
            }

            #sidebar-toggle {
                padding: 0.45rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-wrapper" id="app-wrapper">

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="bi bi-heart-pulse-fill"></i></div>
            <a href="{{ route('panel.dashboard') }}" class="text-decoration-none d-flex flex-column">
                <span class="brand-text">InnApp</span>
                <span class="brand-sub">{{ auth()->user()->specialty?->name ?? 'İstifadəçi' }}</span>
            </a>
        </div>

        <div class="sidebar-nav-container">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('panel.dashboard') }}" class="nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" title="Dashboard">
                        <i class="bi bi-grid-1x2"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('panel.patients.index') }}" class="nav-link {{ request()->routeIs('doctor.patients*') ? 'active' : '' }}" title="Müştərilər">
                        <i class="bi bi-people"></i><span>Müştərilər</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('panel.appointments.index') }}" class="nav-link {{ request()->routeIs('doctor.appointments*') ? 'active' : '' }}" title="Randevular">
                        <i class="bi bi-calendar-check"></i><span>Randevular</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('panel.calendar.index') }}" class="nav-link {{ request()->routeIs('doctor.calendar*') ? 'active' : '' }}" title="Təqvim">
                        <i class="bi bi-calendar3"></i><span>Təqvim</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('panel.treatment-types.index') }}" class="nav-link {{ request()->routeIs('doctor.treatment-types*') ? 'active' : '' }}" title="Xidmət Növləri">
                        <i class="bi bi-list-check"></i><span>Xidmət Növləri</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('panel.subscription.index') }}" class="nav-link {{ request()->routeIs('doctor.subscription*') ? 'active' : '' }}" title="Abunəlik">
                        <i class="bi bi-shield-check"></i><span>Abunəlik</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('panel.reports.revenue') }}" class="nav-link {{ request()->routeIs('doctor.reports*') ? 'active' : '' }}" title="Hesabat">
                        <i class="bi bi-bar-chart-line"></i><span>Hesabat</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('panel.sms-templates.index') }}" class="nav-link {{ request()->routeIs('panel.sms-templates*') ? 'active' : '' }}" title="SMS Şablonları">
                        <i class="bi bi-chat-dots"></i><span>SMS Şablonları</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('panel.profile.edit') }}" class="nav-link {{ request()->routeIs('panel.profile*') ? 'active' : '' }}" title="Profil">
                        <i class="bi bi-person-circle"></i><span>Profil</span>
                    </a>
                </li>
            </ul>
        </div>

        @php $subscription = auth()->user()->activeSubscription()->with('package')->first(); @endphp
        @if($subscription && !auth()->user()->is_demo)
        <div class="sub-info-block">
            <div class="sub-pkg">{{ $subscription->package->name }}</div>
            <div class="sub-row">
                <span>Müştəri</span>
                <span>{{ $subscription->patients_used }}/{{ $subscription->package->patient_limit ?? '∞' }}</span>
            </div>
            <div class="sub-row">
                <span>SMS</span>
                <span>{{ $subscription->sms_used }}/{{ $subscription->package->sms_limit ?? '∞' }}</span>
            </div>
            <div class="sub-exp">
                <i class="bi bi-clock-history"></i>
                Son: {{ $subscription->expires_at->format('d.m.Y') }}
            </div>
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
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button id="sidebar-toggle" title="Menunu gizlət / göstər" aria-label="Toggle Sidebar">
                    <i class="bi bi-list" id="toggle-icon"></i>
                </button>
                <span class="page-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="user-badge">İstifadəçi</span>
                <span class="user-label fw-semibold text-dark">{{ auth()->user()->full_name }}</span>
            </div>
        </header>

        @if(auth()->user()->is_demo)
        @php $remaining = now()->diffInMinutes(auth()->user()->demo_expires_at, false); @endphp
        <div class="demo-banner">
            <div class="demo-title">
                <i class="bi bi-play-circle-fill"></i>
                <span>Demo rejimi — 
                    @if($remaining > 0)
                        <span id="demo-timer" data-minutes="{{ $remaining }}">{{ $remaining }} dəqiqə qalıb</span>
                    @else
                        <span>Müddət bitib</span>
                    @endif
                </span>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <span style="opacity: 0.9; font-size: 0.825rem">Bəyəndiniz? Öz hesabınızı yaradın</span>
                <form method="POST" action="{{ route('demo.exit') }}" style="margin:0">
                    @csrf
                    <button type="submit" class="demo-cta-btn">
                        <i class="bi bi-rocket-takeoff me-1"></i>Qeydiyyat
                    </button>
                </form>
            </div>
        </div>
        @endif

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
