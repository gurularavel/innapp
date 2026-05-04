<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş — InnApp</title>
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
        .form-label { font-size: .875rem; font-weight: 600; color: var(--af-dark); margin-bottom: 6px; }
        .form-control {
            border-radius: 8px;
            border: 1.5px solid #deeaf8;
            padding: 10px 14px;
            font-size: .9rem;
            transition: border-color .2s, box-shadow .2s;
            background: var(--af-ice-lt);
        }
        .form-control:focus {
            border-color: var(--af-steel);
            box-shadow: 0 0 0 3px rgba(74,111,165,.15);
            background: #fff;
        }
        .input-group-text {
            border-radius: 8px 0 0 8px;
            border: 1.5px solid #deeaf8;
            border-right: none;
            background: var(--af-ice-lt);
            color: var(--af-steel);
        }
        .input-group .form-control { border-radius: 0 8px 8px 0; border-left: none; }
        .input-group:focus-within .input-group-text {
            border-color: var(--af-steel);
            background: #fff;
        }
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
        .form-check-input:checked { background-color: var(--af-steel); border-color: var(--af-steel); }
        .is-invalid { border-color: #dc3545 !important; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">

            <div class="text-center mb-4">
                <a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                    <i class="bi bi-grid-fill" style="font-size:1.6rem;color:var(--af-ice)"></i>
                    <div class="brand-name">Inn<span>App</span></div>
                </a>
                <p class="mt-1 mb-0" style="color:rgba(255,255,255,.55);font-size:.88rem">Randevu İdarəetmə Sistemi</p>
            </div>

            <div class="auth-card">
                <div class="card-body p-4 p-md-5">
                    <h5 class="fw-bold mb-4" style="color:var(--af-dark)">Hesabınıza daxil olun</h5>

                    @if(session('status'))
                        <div class="alert alert-info py-2 small">{{ session('status') }}</div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger py-2 small">
                        @foreach($errors->all() as $error)
                            <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
                        @endforeach
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="email">E-poçt</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="email@example.com" autofocus required autocomplete="username">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Şifrə</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                    placeholder="••••••••" required autocomplete="current-password">
                            </div>
                        </div>

                        <div class="mb-4 d-flex align-items-center justify-content-between">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label text-muted small" for="remember">Məni xatırla</label>
                            </div>
                            @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="auth-link small">Şifrəni unutdum?</a>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-auth w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Daxil ol
                        </button>
                    </form>

                    <hr class="my-4" style="border-color:#e8f0fb">
                    <p class="text-center mb-0 small text-muted">
                        Hesabınız yoxdur?
                        <a href="{{ route('register') }}" class="auth-link">Qeydiyyatdan keçin</a>
                    </p>
                </div>
            </div>

            <p class="text-center mt-3">
                <a href="{{ route('home') }}" style="color:rgba(255,255,255,.4);text-decoration:none;font-size:.77rem">
                    <i class="bi bi-arrow-left me-1"></i>Ana səhifəyə qayıt
                </a>
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
