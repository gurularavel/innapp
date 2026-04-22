<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'InnApp — Klinika İdarəetmə Sistemi')</title>
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon/favicon-96x96.png">
    <link rel="apple-touch-icon" href="/favicon/apple-touch-icon.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-X8ZGYKVJ4V"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-X8ZGYKVJ4V');
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --primary:      #0e86d4;
            --primary-dk:   #0a6daf;
            --primary-lt:   #e8f4fd;
            --teal:         #1bc8c8;
            --teal-lt:      #e4f9f9;
            --dark:         #0e1e35;
            --mid:          #2c4160;
            --muted:        #6b7fa3;
            --body-bg:      #ffffff;
            --section-alt:  #f5f9ff;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; color: var(--dark); background: var(--body-bg); margin: 0; }

        /* ── Navbar ── */
        .navbar-public {
            background: rgba(255,255,255,.97);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 14px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 0 #e8edf4, 0 4px 16px rgba(14,134,212,.06);
            transition: box-shadow .25s;
        }
        .navbar-public .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .navbar-public .brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--teal));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 3px 10px rgba(14,134,212,.3);
        }
        .navbar-public .brand-name {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -.4px;
        }
        .navbar-public .brand-name span { color: var(--primary); }
        .navbar-public .nav-link {
            color: var(--mid) !important;
            font-weight: 500;
            font-size: .9rem;
            padding: 6px 14px !important;
            border-radius: 8px;
            transition: color .2s, background .2s;
        }
        .navbar-public .nav-link:hover { color: var(--primary) !important; background: var(--primary-lt); }
        .btn-nav-login {
            border: 1.5px solid #d4dce8;
            color: var(--mid) !important;
            border-radius: 8px;
            padding: 7px 18px !important;
            font-weight: 600;
            font-size: .88rem;
            transition: all .2s;
            text-decoration: none;
        }
        .btn-nav-login:hover { border-color: var(--primary); color: var(--primary) !important; background: var(--primary-lt); }
        .btn-nav-register {
            background: var(--primary);
            color: #fff !important;
            border-radius: 8px;
            padding: 7px 18px !important;
            font-weight: 700;
            font-size: .88rem;
            border: none;
            transition: all .2s;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(14,134,212,.25);
        }
        .btn-nav-register:hover { background: var(--primary-dk); transform: translateY(-1px); box-shadow: 0 5px 15px rgba(14,134,212,.35); color: #fff !important; }

        /* ── Footer ── */
        .footer-public {
            background: var(--dark);
            color: rgba(255,255,255,.6);
            padding: 56px 0 28px;
        }
        .footer-public .brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--teal));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.1rem;
        }
        .footer-public .brand-name { color: #fff; font-size: 1.2rem; font-weight: 800; }
        .footer-public .brand-name span { color: var(--teal); }
        .footer-public a { color: rgba(255,255,255,.5); text-decoration: none; transition: color .2s; }
        .footer-public a:hover { color: var(--teal); }
        .footer-divider { border-color: rgba(255,255,255,.1); }
        .footer-heading { color: #fff; font-weight: 600; font-size: .75rem; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 14px; }
        .footer-social-btn {
            width: 34px; height: 34px;
            border-radius: 8px;
            background: rgba(255,255,255,.08);
            display: flex; align-items: center; justify-content: center;
            color: rgba(255,255,255,.5);
            transition: all .2s;
            text-decoration: none;
        }
        .footer-social-btn:hover { background: rgba(27,200,200,.2); color: var(--teal); }
    </style>
    @yield('styles')
    @stack('styles')
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-public">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <div class="brand-icon"><i class="bi bi-heart-pulse-fill"></i></div>
            <span class="brand-name">Inn<span>App</span></span>
        </a>
        <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-3" style="color:var(--mid)"></i>
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
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('panel.dashboard') }}" class="btn-nav-register">
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
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-icon"><i class="bi bi-heart-pulse-fill"></i></div>
                    <span class="brand-name">Inn<span>App</span></span>
                </div>
                <p class="small mb-3" style="color:rgba(255,255,255,.45);line-height:1.8;">
                    Klinika üçün hərtərəfli idarəetmə sistemi.<br>
                    Randevular, xəstələr, SMS — hamısı bir yerdə.
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="footer-social-btn"><i class="bi bi-facebook" style="font-size:.85rem"></i></a>
                    <a href="#" class="footer-social-btn"><i class="bi bi-instagram" style="font-size:.85rem"></i></a>
                    <a href="#" class="footer-social-btn"><i class="bi bi-linkedin" style="font-size:.85rem"></i></a>
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
                <ul class="list-unstyled small" style="color:rgba(255,255,255,.5)">
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-fill" style="color:var(--teal);font-size:.85rem"></i>
                        info@innapp.az
                    </li>
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-telephone-fill" style="color:var(--teal);font-size:.85rem"></i>
                        +994 55 703 80 08
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill" style="color:var(--teal);font-size:.85rem"></i>
                        Bakı, Azərbaycan
                    </li>
                </ul>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <small style="color:rgba(255,255,255,.3)">© {{ date('Y') }} InnApp. Bütün hüquqlar qorunur.</small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
