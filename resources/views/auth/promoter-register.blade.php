<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotor Qeydiyyatı — InnApp</title>
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
        .auth-brand { text-decoration: none; }
        .auth-brand .brand-name { color: #fff; font-size: 1.5rem; font-weight: 800; letter-spacing: -.5px; }
        .auth-brand .brand-name span { color: var(--af-ice); }
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
        .auth-divider { color: #94a3b8; font-size: .82rem; }
        .auth-link { color: var(--af-steel); font-weight: 600; text-decoration: none; }
        .auth-link:hover { color: var(--af-steel-dk); text-decoration: underline; }
        .form-check-input:checked { background-color: var(--af-steel); border-color: var(--af-steel); }
        .is-invalid { border-color: #dc3545 !important; }
        .promo-benefits {
            background: var(--af-ice-lt);
            border: 1.5px solid #deeaf8;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: .85rem;
            color: var(--af-dark);
        }
        .promo-benefits .bi { color: var(--af-steel); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="text-center mb-4">
                <a class="auth-brand d-inline-flex align-items-center gap-2 text-decoration-none" href="{{ route('home') }}">
                    <i class="bi bi-grid-fill" style="font-size:1.6rem;color:var(--af-ice)"></i>
                    <div class="brand-name">Inn<span>App</span></div>
                </a>
                <p class="mt-1 mb-0" style="color:rgba(255,255,255,.55);font-size:.88rem">Promotor Tərəfdaşlıq Proqramı</p>
            </div>

            <div class="auth-card">
                <div class="card-body p-4 p-md-5">
                    <h5 class="fw-bold mb-3" style="color:var(--af-dark)">Promotor hesabı yaradın</h5>

                    <div class="promo-benefits mb-4">
                        <div class="mb-1"><i class="bi bi-ticket-perforated me-2"></i>Qeydiyyatdan dərhal sonra <strong>şəxsi promo kodunuz</strong> avtomatik yaradılır</div>
                        <div class="mb-1"><i class="bi bi-percent me-2"></i>Müştəriləriniz üçün <strong>{{ rtrim(rtrim(number_format($discountPercent, 2), '0'), '.') }}% qeydiyyat endirimi</strong></div>
                        <div><i class="bi bi-cash-coin me-2"></i>Hər uğurlu ödənişdən <strong>{{ rtrim(rtrim(number_format($commissionPercent, 2), '0'), '.') }}% komissiya</strong></div>
                    </div>

                    @if($errors->any())
                    <div class="alert alert-danger py-2 small">
                        @foreach($errors->all() as $error)
                            <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
                        @endforeach
                    </div>
                    @endif

                    <form method="POST" action="{{ route('promoter.register') }}">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label" for="name">Ad</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" placeholder="Adınız" autofocus required autocomplete="given-name">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="surname">Soyad</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" id="surname" name="surname" class="form-control @error('surname') is-invalid @enderror"
                                        value="{{ old('surname') }}" placeholder="Soyadınız" required autocomplete="family-name">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="email">E-poçt</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="email@example.com" required autocomplete="username">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="phone">Telefon <span class="text-muted fw-normal">(ixtiyari)</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone') }}" placeholder="+994 55 123 45 67" autocomplete="tel">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Şifrə</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Minimum 8 simvol" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="password_confirmation">Şifrəni təsdiq edin</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                                    placeholder="Şifrəni təkrar daxil edin" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox"
                                       id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
                                <label class="form-check-label small text-muted" for="terms">
                                    <a href="#" class="auth-link" data-bs-toggle="modal" data-bs-target="#termsModal">{{ $termsTitle ?? 'İstifadə qaydaları' }}</a>nı oxudum və qəbul edirəm
                                </label>
                            </div>
                            @error('terms')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-auth w-100">
                            <i class="bi bi-megaphone me-2"></i>Promotor ol
                        </button>
                    </form>

                    <hr class="my-4 auth-divider">
                    <p class="text-center mb-0 small text-muted">
                        Artıq hesabınız var?
                        <a href="{{ route('login') }}" class="auth-link">Daxil olun</a>
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
<!-- İstifadə qaydaları modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content" style="border:none;border-radius:14px;">
            <div class="modal-header" style="background:var(--af-ice-lt);">
                <h6 class="modal-title fw-bold" id="termsModalLabel" style="color:var(--af-dark)">
                    <i class="bi bi-file-earmark-text me-2" style="color:var(--af-steel)"></i>{{ $termsTitle ?? 'İstifadə Qaydaları' }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bağla"></button>
            </div>
            <div class="modal-body" style="font-size:.9rem;color:#334155;line-height:1.6;">
                @if(trim($termsContent ?? '') !== '')
                    {!! nl2br(e($termsContent)) !!}
                @else
                    <p class="text-muted mb-0">İstifadə qaydaları hələ əlavə edilməyib.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-auth px-4" data-bs-dismiss="modal">Bağla</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
