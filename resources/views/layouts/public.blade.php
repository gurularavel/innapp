<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'InnApp — Klinika İdarəetmə Sistemi')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        /* ── Arctic Frost Theme ── */
        :root {
            --af-steel:    #4a6fa5;
            --af-steel-dk: #3a5a8c;
            --af-ice:      #d4e4f7;
            --af-ice-lt:   #edf4fd;
            --af-silver:   #c0c0c0;
            --af-white:    #fafafa;
            --af-dark:     #1e2d3d;
            --af-mid:      #3d5166;
            --af-muted:    #64748b;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            color: var(--af-dark);
            background: var(--af-white);
            margin: 0;
        }

        /* ── Navbar ── */
        .navbar-public {
            background: rgba(74, 111, 165, 0.97);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 14px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 24px rgba(74, 111, 165, 0.25);
        }
        .navbar-public .navbar-brand {
            font-size: 1.45rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
            text-decoration: none;
        }
        .navbar-public .navbar-brand span { color: var(--af-ice); }
        .navbar-public .nav-link {
            color: rgba(255,255,255,.85) !important;
            font-weight: 500;
            font-size: .92rem;
            padding: 6px 14px !important;
            transition: color .2s;
        }
        .navbar-public .nav-link:hover { color: #fff !important; }
        .btn-nav-login {
            border: 1.5px solid rgba(255,255,255,.5);
            color: #fff !important;
            border-radius: 8px;
            padding: 6px 18px !important;
            font-weight: 600;
            font-size: .9rem;
            transition: all .2s;
            text-decoration: none;
        }
        .btn-nav-login:hover { background: rgba(255,255,255,.18); border-color: #fff; }
        .btn-nav-register {
            background: #fff;
            color: var(--af-steel) !important;
            border-radius: 8px;
            padding: 6px 18px !important;
            font-weight: 700;
            font-size: .9rem;
            border: none;
            transition: all .2s;
            text-decoration: none;
        }
        .btn-nav-register:hover { background: var(--af-ice); }

        /* ── Footer ── */
        .footer-public {
            background: var(--af-dark);
            color: rgba(255,255,255,.65);
            padding: 52px 0 28px;
        }
        .footer-public .brand { color: #fff; font-size: 1.3rem; font-weight: 800; }
        .footer-public .brand span { color: var(--af-ice); }
        .footer-public a { color: rgba(255,255,255,.55); text-decoration: none; transition: color .2s; }
        .footer-public a:hover { color: var(--af-ice); }
        .footer-divider { border-color: rgba(255,255,255,.12); }
        .footer-heading { color: #fff; font-weight: 600; font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 14px; }
    </style>
    @yield('styles')
    @stack('styles')
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-public">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            Inn<span>App</span>
        </a>
        <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span style="color:#fff"><i class="bi bi-list fs-3"></i></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#features">Xüsusiyyətlər</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#how-it-works">Necə işləyir</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#packages">Paketlər</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#contact">Əlaqə</a></li>
            </ul>
            <div class="d-flex gap-2 mt-2 mt-lg-0 align-items-center">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('panel.dashboard') }}" class="btn-nav-register nav-link">
                        <i class="bi bi-grid me-1"></i>Panelə keç
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-nav-login">Daxil ol</a>
                    <a href="{{ route('register') }}" class="btn-nav-register">Qeydiyyat</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

@yield('content')

<!-- Footer -->
<footer class="footer-public" id="contact">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="brand mb-3">Inn<span>App</span></div>
                <p class="small mb-3" style="color:rgba(255,255,255,.5);line-height:1.75;">
                    Klinika üçün hərtərəfli idarəetmə sistemi.<br>
                    Randevular, xəstələr, SMS — hamısı bir yerdə.
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="d-flex align-items-center justify-content-center rounded-circle" style="width:34px;height:34px;background:rgba(255,255,255,.1);color:rgba(255,255,255,.6);transition:.2s" onmouseover="this.style.background='rgba(212,228,247,.2)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">
                        <i class="bi bi-facebook" style="font-size:.85rem"></i>
                    </a>
                    <a href="#" class="d-flex align-items-center justify-content-center rounded-circle" style="width:34px;height:34px;background:rgba(255,255,255,.1);color:rgba(255,255,255,.6);transition:.2s" onmouseover="this.style.background='rgba(212,228,247,.2)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">
                        <i class="bi bi-instagram" style="font-size:.85rem"></i>
                    </a>
                    <a href="#" class="d-flex align-items-center justify-content-center rounded-circle" style="width:34px;height:34px;background:rgba(255,255,255,.1);color:rgba(255,255,255,.6);transition:.2s" onmouseover="this.style.background='rgba(212,228,247,.2)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">
                        <i class="bi bi-linkedin" style="font-size:.85rem"></i>
                    </a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="footer-heading">Sistem</div>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="{{ route('home') }}#features">Xüsusiyyətlər</a></li>
                    <li class="mb-2"><a href="{{ route('home') }}#how-it-works">Necə işləyir</a></li>
                    <li class="mb-2"><a href="{{ route('home') }}#packages">Paketlər</a></li>
                    <li><a href="{{ route('register') }}">Qeydiyyat</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <div class="footer-heading">Hesab</div>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="{{ route('login') }}">Daxil ol</a></li>
                    <li><a href="{{ route('register') }}">Yeni hesab</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <div class="footer-heading">Əlaqə</div>
                <ul class="list-unstyled small" style="color:rgba(255,255,255,.55)">
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-fill" style="color:var(--af-ice);font-size:.85rem"></i>
                        info@innapp.az
                    </li>
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-telephone-fill" style="color:var(--af-ice);font-size:.85rem"></i>
                        +994 12 000 00 00
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill" style="color:var(--af-ice);font-size:.85rem"></i>
                        Bakı, Azərbaycan
                    </li>
                </ul>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <small style="color:rgba(255,255,255,.35)">© {{ date('Y') }} InnApp. Bütün hüquqlar qorunur.</small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
