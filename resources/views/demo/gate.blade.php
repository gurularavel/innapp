<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon/favicon-96x96.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <title>Demo — InnApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --af-steel:    #4a6fa5;
            --af-steel-dk: #3a5a8c;
            --af-ice:      #d4e4f7;
            --af-ice-lt:   #edf4fd;
            --af-dark:     #1e2d3d;
        }
        body {
            background: linear-gradient(135deg, #1e2d3d 0%, #2d4a6e 55%, #3a5a8c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }
        .auth-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 24px 64px rgba(0,0,0,.32);
            background: #fff;
        }
        .brand-name { color: #fff; font-size: 1.5rem; font-weight: 800; letter-spacing: -.5px; }
        .brand-name span { color: var(--af-ice); }
        .btn-auth {
            background: var(--af-steel);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 700;
            font-size: .95rem;
            transition: all .22s;
        }
        .btn-auth:hover {
            background: var(--af-steel-dk);
            color: #fff;
            box-shadow: 0 6px 20px rgba(74,111,165,.35);
            transform: translateY(-1px);
        }
        .auth-link { color: var(--af-steel); font-weight: 600; text-decoration: none; }
        .auth-link:hover { color: var(--af-steel-dk); text-decoration: underline; }
        .demo-perk { font-size: .875rem; color: #475569; }
        .demo-perk i { color: var(--af-steel); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="text-center mb-4">
                <a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                    <i class="bi bi-grid-fill" style="font-size:1.6rem;color:var(--af-ice)"></i>
                    <div class="brand-name">Inn<span>App</span></div>
                </a>
                <p class="mt-1 mb-0" style="color:rgba(255,255,255,.55);font-size:.88rem">Randevu İdarəetmə Sistemi</p>
            </div>

            <div class="auth-card">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <h5 class="fw-bold mb-1" style="color:var(--af-dark)">Demo hesab yarat</h5>
                        <p class="text-muted small mb-0">
                            Qeydiyyat tələb olunmur. Hazır məlumatlarla dolu test müəssisəsi yaradılacaq.
                        </p>
                    </div>

                    @if($errors->any())
                    <div class="alert alert-danger py-2 small mb-4">
                        @foreach($errors->all() as $error)
                            <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
                        @endforeach
                    </div>
                    @endif

                    <ul class="list-unstyled mb-4">
                        <li class="demo-perk mb-2"><i class="bi bi-check-circle-fill me-2"></i>2 saat ərzində bütün funksiyalar açıqdır</li>
                        <li class="demo-perk mb-2"><i class="bi bi-check-circle-fill me-2"></i>Nümunə müştəri, randevu və xidmətlərlə hazır gəlir</li>
                        <li class="demo-perk"><i class="bi bi-check-circle-fill me-2"></i>Müddət bitəndə bütün məlumatlar avtomatik silinir</li>
                    </ul>

                    <form method="POST" action="{{ route('demo.create') }}">
                        @csrf

                        @include('auth._turnstile', ['form' => 'demo'])

                        <button type="submit" class="btn btn-auth w-100">
                            <i class="bi bi-play-circle me-2"></i>Demoya başla
                        </button>
                    </form>

                    <hr class="my-4">
                    <p class="text-center mb-0 small text-muted">
                        Real hesab açmaq istəyirsiniz?
                        <a href="{{ route('register') }}" class="auth-link">Qeydiyyatdan keçin</a>
                    </p>
                </div>
            </div>

            <p class="text-center mt-3" style="color:rgba(255,255,255,.3);font-size:.77rem">
                <a href="{{ route('home') }}" style="color:rgba(255,255,255,.4);text-decoration:none">
                    <i class="bi bi-arrow-left me-1"></i>Ana səhifəyə qayıt
                </a>
            </p>
        </div>
    </div>
</div>

<script @cspNonce src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@include('layouts._whatsapp_float')
</body>
</html>
