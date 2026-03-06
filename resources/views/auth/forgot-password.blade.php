<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifrəni Unutdum — InnApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f4c75 0%, #1b6ca8 50%, #2196f3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <i class="bi bi-grid-fill text-white" style="font-size: 3rem;"></i>
                <h3 class="text-white mt-2 fw-bold">InnApp</h3>
                <p class="text-white-50">İdarəetmə Sistemi</p>
            </div>
            <div class="card login-card">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3 fw-semibold text-center">Şifrəni Sıfırla</h5>
                    <p class="text-muted small mb-4">E-poçt ünvanınızı daxil edin. Şifrə sıfırlama linki göndəriləcək.</p>

                    @if(session('status'))
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-medium">E-poçt</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="sizin@email.com" autofocus required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-send me-2"></i>Sıfırlama Linki Göndər
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="text-muted small text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Girişə qayıt
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
