<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'InnApp randevu idarəetmə sistemi')">
    <title>@yield('title', 'InnApp | Randevu idarəetmə sistemi')</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon/favicon-96x96.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/elegant-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/flaticon-set.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/magnific-popup.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/owl.theme.default.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/helper.css') }}" rel="stylesheet">
    <link href="{{ asset('style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #0e86d4;
            --brand-secondary: #1bc8c8;
            --brand-dark: #0e1e35;
        }

        .brand-link {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .brand-link:hover,
        .brand-link:focus {
            text-decoration: none;
        }

        .brand-badge {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            box-shadow: 0 16px 30px rgba(14, 134, 212, 0.22);
            flex-shrink: 0;
        }

        .brand-badge img {
            width: 26px;
            height: 26px;
            display: block;
        }

        .brand-copy {
            display: flex;
            flex-direction: column;
            line-height: 1;
        }

        .brand-copy strong {
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .brand-copy span {
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.68rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            margin-top: 6px;
        }

        .btn-theme.btn-md,
        .btn-theme.effect.btn-sm,
        .btn-theme.border.btn-md,
        .btn-light.border.btn-md {
            text-transform: none;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(7, 22, 40, 0.88);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .site-header.scrolled {
            background: rgba(255,255,255,.94);
            border-bottom-color: rgba(14,30,53,.08);
            box-shadow: 0 14px 34px rgba(14,30,53,.08);
        }

        .site-header.scrolled .brand-copy strong {
            color: var(--brand-dark);
        }

        .site-header.scrolled .brand-copy span {
            color: rgba(14, 30, 53, 0.55);
        }

        .site-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            min-height: 84px;
        }

        .site-nav-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .site-nav-menu a {
            display: inline-flex;
            align-items: center;
            min-height: 44px;
            padding: 0 16px;
            border-radius: 999px;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: background .2s ease, color .2s ease, transform .2s ease;
        }

        .site-nav-menu a:hover,
        .site-nav-menu a:focus {
            background: rgba(255,255,255,.10);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .site-header.scrolled .site-nav-menu a {
            color: var(--brand-dark);
        }

        .site-header.scrolled .site-nav-menu a:hover,
        .site-header.scrolled .site-nav-menu a:focus {
            background: rgba(14,134,212,.10);
            color: var(--brand-dark);
        }

        .site-nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .site-link-login {
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
        }

        .site-link-login:hover,
        .site-link-login:focus {
            color: #ffffff;
            opacity: .82;
        }

        .site-header.scrolled .site-link-login {
            color: var(--brand-dark);
        }

        .site-link-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 22px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 16px 28px rgba(14,134,212,.18);
        }

        .site-link-cta:hover,
        .site-link-cta:focus {
            color: #ffffff;
        }

        .site-menu-toggle {
            display: none;
            width: 48px;
            height: 48px;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 14px;
            background: rgba(255,255,255,.08);
            color: #ffffff;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .site-header.scrolled .site-menu-toggle {
            color: var(--brand-dark);
            border-color: rgba(14,30,53,.1);
            background: rgba(14,30,53,.04);
        }

        .site-mobile-panel {
            position: fixed;
            top: 0;
            right: 0;
            width: min(360px, 88vw);
            height: 100vh;
            background: linear-gradient(180deg, #071628 0%, #0c2440 100%);
            transform: translateX(100%);
            transition: transform .28s ease;
            z-index: 1002;
            padding: 22px;
            overflow-y: auto;
            box-shadow: -18px 0 40px rgba(0,0,0,.28);
        }

        .site-mobile-panel.open {
            transform: translateX(0);
        }

        .site-mobile-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 28px;
        }

        .site-mobile-close {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.08);
            color: #ffffff;
            flex-shrink: 0;
        }

        .site-mobile-menu {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 10px;
        }

        .site-mobile-menu a {
            display: flex;
            align-items: center;
            min-height: 52px;
            padding: 0 16px;
            border-radius: 14px;
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
            background: rgba(255,255,255,.05);
        }

        .site-mobile-auth {
            display: grid;
            gap: 12px;
            margin-top: 24px;
        }

        .site-mobile-auth a {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 700;
        }

        .site-mobile-auth .site-mobile-login {
            color: #ffffff;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
        }

        .site-mobile-auth .site-mobile-cta {
            color: #ffffff;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
        }

        .site-overlay {
            position: fixed;
            inset: 0;
            background: rgba(7,22,40,.46);
            opacity: 0;
            visibility: hidden;
            transition: opacity .25s ease, visibility .25s ease;
            z-index: 1001;
        }

        .site-overlay.open {
            opacity: 1;
            visibility: visible;
        }

        .footer-note p,
        .footer-note a {
            margin-bottom: 0;
        }

        footer .brand-copy strong {
            color: var(--brand-dark);
        }

        footer .brand-copy span {
            color: rgba(14, 30, 53, 0.5);
        }

        .contact-form .form-control,
        .subscribe .form-control {
            color: #0f172a;
        }

        @media (max-width: 1023px) {
            body.menu-open {
                overflow: hidden;
            }

            .brand-badge {
                width: 42px;
                height: 42px;
                border-radius: 12px;
            }

            .brand-copy strong {
                font-size: 1rem;
            }

            .brand-copy span {
                font-size: 0.62rem;
                margin-top: 4px;
            }

            .site-nav-menu,
            .site-nav-actions {
                display: none !important;
            }

            .site-menu-toggle {
                display: inline-flex;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

    <div id="preloader">
        <div id="softing-preloader" class="softing-preloader">
            <div class="animation-preloader">
                <div class="spinner"></div>
                <div class="txt-loading">
                    @foreach(str_split('INNAPP') as $letter)
                        <span data-text-preloader="{{ $letter }}" class="letters-loading">{{ $letter }}</span>
                    @endforeach
                </div>
            </div>
            <div class="loader">
                <div class="row">
                    @for($i = 0; $i < 4; $i++)
                        <div class="col-3 loader-section {{ $i < 2 ? 'section-left' : 'section-right' }}">
                            <div class="bg"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    <header id="home" class="site-header">
        <div class="container">
            <div class="site-nav">
                <a class="brand-link" href="{{ route('home') }}">
                    <span class="brand-badge">
                        <img src="{{ asset('favicon/favicon.svg') }}" alt="InnApp">
                    </span>
                    <span class="brand-copy">
                        <strong>InnApp</strong>
                        <span>Randevu sistemi</span>
                    </span>
                </a>

                <ul class="site-nav-menu">
                    <li><a href="#home">Ana səhifə</a></li>
                    <li><a href="#about">Haqqımızda</a></li>
                    <li><a href="#features">Xüsusiyyətlər</a></li>
                    <li><a href="#overview">Üstünlüklər</a></li>
                    <li><a href="#pricing">Paketlər</a></li>
                    <li><a href="#contact">Əlaqə</a></li>
                </ul>

                <div class="site-nav-actions">
                    @auth
                        <a class="site-link-cta" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('panel.dashboard') }}">Panelə keç</a>
                    @else
                        <a class="site-link-login" href="{{ route('login') }}">Daxil ol</a>
                        <a class="site-link-cta" href="{{ route('register') }}">Pulsuz başla</a>
                    @endauth
                </div>

                <button type="button" class="site-menu-toggle" aria-label="Menyunu aç">
                    <i class="fa fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <aside class="site-mobile-panel" id="mobile-menu" aria-hidden="true">
        <div class="site-mobile-head">
            <a class="brand-link" href="{{ route('home') }}">
                <span class="brand-badge">
                    <img src="{{ asset('favicon/favicon.svg') }}" alt="InnApp">
                </span>
                <span class="brand-copy">
                    <strong>InnApp</strong>
                    <span>Randevu sistemi</span>
                </span>
            </a>
            <button type="button" class="site-mobile-close" aria-label="Menyunu bağla">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <ul class="site-mobile-menu">
            <li><a href="#home">Ana səhifə</a></li>
            <li><a href="#about">Haqqımızda</a></li>
            <li><a href="#features">Xüsusiyyətlər</a></li>
            <li><a href="#overview">Üstünlüklər</a></li>
            <li><a href="#pricing">Paketlər</a></li>
            <li><a href="#contact">Əlaqə</a></li>
        </ul>

        <div class="site-mobile-auth">
            @auth
                <a class="site-mobile-cta" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('panel.dashboard') }}">Panelə keç</a>
            @else
                <a class="site-mobile-login" href="{{ route('login') }}">Daxil ol</a>
                <a class="site-mobile-cta" href="{{ route('register') }}">Pulsuz başla</a>
            @endauth
        </div>
    </aside>

    <div class="site-overlay"></div>

    @yield('content')

    <footer class="default-padding">
        <div class="container">
            <div class="f-items">
                <div class="row">
                    <div class="col-lg-4 col-md-6 item">
                        <div class="f-item">
                            <a class="brand-link mb-3" href="{{ route('home') }}">
                                <span class="brand-badge">
                                    <img src="{{ asset('favicon/favicon.svg') }}" alt="InnApp">
                                </span>
                                <span class="brand-copy">
                                    <strong>InnApp</strong>
                                    <span>Randevu sistemi</span>
                                </span>
                            </a>
                            <p>
                                Müxtəlif sahələr üçün hazırlanmış vahid randevu platforması.
                                Randevular, xəstə bazası, SMS və hesabatlar bir paneldə.
                            </p>
                            <a href="{{ route('register') }}" class="btn circle btn-theme effect btn-sm">İndi başla</a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 item">
                        <div class="f-item link">
                            <h4>Səhifələr</h4>
                            <ul>
                                <li><a href="#home">Ana səhifə</a></li>
                                <li><a href="#about">Haqqımızda</a></li>
                                <li><a href="#features">Xüsusiyyətlər</a></li>
                                <li><a href="#pricing">Paketlər</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 item">
                        <div class="f-item link">
                            <h4>Hesab</h4>
                            <ul>
                                <li><a href="{{ route('login') }}">Daxil ol</a></li>
                                <li><a href="{{ route('register') }}">Qeydiyyat</a></li>
                                <li><a href="{{ route('demo.start') }}">Demo</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 item">
                        <div class="f-item contact-widget">
                            <div class="address">
                                <ul>
                                    <li>
                                        <div class="icon">
                                            <i class="fas fa-globe"></i>
                                        </div>
                                        <div class="info">
                                            <h5>Veb sayt:</h5>
                                            <span>www.innapp.az</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="info">
                                            <h5>Email:</h5>
                                            <span>info@innapp.az</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="icon">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <div class="info">
                                            <h5>Telefon:</h5>
                                            <span>+994 55 703 80 08</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom footer-note">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-6">
                            <p>&copy; {{ date('Y') }} InnApp. Bütün hüquqlar qorunur.</p>
                        </div>
                        <div class="col-lg-6 text-end link">
                            <ul>
                                <li><a href="#features">Xüsusiyyətlər</a></li>
                                <li><a href="#pricing">Qiymətlər</a></li>
                                <li><a href="#contact">Dəstək</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.appear.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/js/wow.min.js') }}"></script>
    <script src="{{ asset('assets/js/count-to.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const header = document.querySelector('.site-header');
            const toggle = document.querySelector('.site-menu-toggle');
            const panel = document.getElementById('mobile-menu');
            const closeButton = document.querySelector('.site-mobile-close');
            const overlay = document.querySelector('.site-overlay');
            const mobileLinks = document.querySelectorAll('.site-mobile-menu a, .site-mobile-auth a');
            const desktopLinks = document.querySelectorAll('.site-nav-menu a');

            if (!header || !toggle || !panel || !closeButton || !overlay) {
                return;
            }

            const closeMenu = () => {
                panel.classList.remove('open');
                overlay.classList.remove('open');
                document.body.classList.remove('menu-open');
                panel.setAttribute('aria-hidden', 'true');
            };

            const openMenu = () => {
                panel.classList.add('open');
                overlay.classList.add('open');
                document.body.classList.add('menu-open');
                panel.setAttribute('aria-hidden', 'false');
            };

            const smoothTo = (target) => {
                const section = document.querySelector(target);
                if (!section) {
                    return;
                }

                const headerOffset = header.offsetHeight;
                const top = section.getBoundingClientRect().top + window.pageYOffset - headerOffset + 4;
                window.scrollTo({ top, behavior: 'smooth' });
            };

            toggle.addEventListener('click', function () {
                if (panel.classList.contains('open')) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });

            closeButton.addEventListener('click', function () {
                closeMenu();
            });

            overlay.addEventListener('click', function () {
                closeMenu();
            });

            mobileLinks.forEach((link) => {
                link.addEventListener('click', function (event) {
                    const href = link.getAttribute('href');
                    if (href && href.startsWith('#')) {
                        event.preventDefault();
                        closeMenu();
                        smoothTo(href);
                    }
                });
            });

            desktopLinks.forEach((link) => {
                link.addEventListener('click', function (event) {
                    const href = link.getAttribute('href');
                    if (href && href.startsWith('#')) {
                        event.preventDefault();
                        smoothTo(href);
                    }
                });
            });

            window.addEventListener('resize', function () {
                closeMenu();
            });

            const syncHeaderState = () => {
                if (window.scrollY > 20) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            };

            window.addEventListener('scroll', syncHeaderState, { passive: true });
            syncHeaderState();
        });
    </script>

    @stack('scripts')
</body>
</html>
