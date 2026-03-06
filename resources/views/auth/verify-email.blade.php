<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-poçtu Təsdiqlə — InnApp</title>
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
            </div>
            <div class="card login-card">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3 fw-semibold text-center">E-poçtu Təsdiqlə</h5>
                    <p class="text-muted small mb-3">Qeydiyyat zamanı göstərdiyiniz e-poçta göndərilən linkə klikləyin.</p>

                    @if(session('status') == 'verification-link-sent')
                        <div class="alert alert-success small">
                            <i class="bi bi-check-circle me-2"></i>Yeni təsdiq linki göndərildi.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
                            <i class="bi bi-envelope me-2"></i>Təsdiq Linkini Yenidən Göndər
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-box-arrow-right me-2"></i>Çıxış
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
