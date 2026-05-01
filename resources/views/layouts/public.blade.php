<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'InnApp klinika idarəetmə sistemi')">
    <title>@yield('title', 'InnApp | Klinika idarəetmə sistemi')</title>

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
    <link href="{{ asset('assets/css/validnavs.css') }}" rel="stylesheet">
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

        nav.navbar .brand-copy strong {
            color: #ffffff;
        }

        nav.navbar.sticked .brand-copy strong,
        nav.navbar.no-background .brand-copy strong {
            color: #ffffff;
        }

        nav.navbar.navbar-sticky.sticked .brand-copy strong {
            color: var(--brand-dark);
        }

        nav.navbar.navbar-sticky.sticked .brand-copy span {
            color: rgba(14, 30, 53, 0.55);
        }

        nav.navbar.navbar-sticky.sticked .brand-badge {
            box-shadow: 0 12px 24px rgba(14, 134, 212, 0.18);
        }

        .navbar-brand img.logo {
            max-height: 42px;
        }

        .navbar .attr-nav .button a,
        .btn-theme.btn-md,
        .btn-theme.effect.btn-sm,
        .btn-theme.border.btn-md,
        .btn-light.border.btn-md {
            text-transform: none;
        }

        .auth-cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .auth-cta .secondary-link {
            color: #ffffff;
            font-weight: 600;
            margin-right: 18px;
        }

        .auth-cta .secondary-link:hover {
            color: #ffffff;
            opacity: 0.85;
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

    <header id="home">
        <nav class="navbar mobile-sidenav navbar-sticky navbar-default validnavs navbar-fixed white no-background">
            <div class="container d-flex justify-content-between align-items-center">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                        <i class="fa fa-bars"></i>
                    </button>
                    <a class="navbar-brand brand-link" href="{{ route('home') }}">
                        <span class="brand-badge">
                            <img src="{{ asset('favicon/favicon.svg') }}" alt="InnApp">
                        </span>
                        <span class="brand-copy">
                            <strong>InnApp</strong>
                            <span>Klinika sistemi</span>
                        </span>
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="navbar-menu">
                    <a class="brand-link d-flex d-lg-none mb-4" href="{{ route('home') }}">
                        <span class="brand-badge">
                            <img src="{{ asset('favicon/favicon.svg') }}" alt="InnApp">
                        </span>
                        <span class="brand-copy">
                            <strong>InnApp</strong>
                            <span>Klinika sistemi</span>
                        </span>
                    </a>
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                        <i class="fa fa-times"></i>
                    </button>

                    <ul class="nav navbar-nav navbar-center" data-in="fadeInDown" data-out="fadeOutUp">
                        <li><a class="smooth-menu" href="#home">Ana səhifə</a></li>
                        <li><a class="smooth-menu" href="#about">Haqqımızda</a></li>
                        <li><a class="smooth-menu" href="#features">Xüsusiyyətlər</a></li>
                        <li><a class="smooth-menu" href="#overview">Üstünlüklər</a></li>
                        <li><a class="smooth-menu" href="#pricing">Paketlər</a></li>
                        <li><a class="smooth-menu" href="#contact">Əlaqə</a></li>
                    </ul>
                </div>

                <div class="attr-right">
                    <div class="attr-nav">
                        <ul>
                            <li class="button dark">
                                @auth
                                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('panel.dashboard') }}">Panelə keç</a>
                                @else
                                    <div class="auth-cta">
                                        <a class="secondary-link" href="{{ route('login') }}">Daxil ol</a>
                                        <a href="{{ route('register') }}">Pulsuz başla</a>
                                    </div>
                                @endauth
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="overlay-screen"></div>
        </nav>
    </header>

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
                                    <span>Klinika sistemi</span>
                                </span>
                            </a>
                            <p>
                                Stomatoloji klinikalar üçün hazırlanmış vahid idarəetmə platforması.
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
    <script src="{{ asset('assets/js/validnavs.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    @stack('scripts')
</body>
</html>
