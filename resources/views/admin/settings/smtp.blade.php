@extends('layouts.admin')

@section('title', 'SMTP Ayarları')
@section('page-title', 'SMTP Ayarları (E-poçt)')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-envelope-at me-2 text-primary"></i>SMTP Konfiqurasiyası</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Bu ayarlar şifrə sıfırlama e-poçtları üçün istifadə olunur. Doldurulmazsa sistem <code>MAIL_*</code> .env dəyişənlərini istifadə edir.
                </p>

                <form method="POST" action="{{ route('admin.settings.smtp.save') }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-sm-8">
                            <label for="smtp_host" class="form-label fw-medium">SMTP Host</label>
                            <input type="text"
                                   id="smtp_host"
                                   name="smtp_host"
                                   class="form-control @error('smtp_host') is-invalid @enderror"
                                   value="{{ old('smtp_host', $settings['smtp_host']) }}"
                                   placeholder="smtp.gmail.com"
                                   required>
                            @error('smtp_host')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label for="smtp_port" class="form-label fw-medium">Port</label>
                            <input type="number"
                                   id="smtp_port"
                                   name="smtp_port"
                                   class="form-control @error('smtp_port') is-invalid @enderror"
                                   value="{{ old('smtp_port', $settings['smtp_port']) }}"
                                   min="1" max="65535"
                                   required>
                            @error('smtp_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label for="smtp_encryption" class="form-label fw-medium">Şifrələmə</label>
                            <select id="smtp_encryption" name="smtp_encryption"
                                    class="form-select @error('smtp_encryption') is-invalid @enderror">
                                @foreach(['tls' => 'TLS (tövsiyə edilir)', 'ssl' => 'SSL', 'none' => 'Heç biri'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('smtp_encryption', $settings['smtp_encryption']) === $val)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('smtp_encryption')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-8">
                            <label for="smtp_username" class="form-label fw-medium">İstifadəçi adı (e-poçt)</label>
                            <input type="text"
                                   id="smtp_username"
                                   name="smtp_username"
                                   class="form-control @error('smtp_username') is-invalid @enderror"
                                   value="{{ old('smtp_username', $settings['smtp_username']) }}"
                                   placeholder="noreply@example.com"
                                   required>
                            @error('smtp_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="smtp_password" class="form-label fw-medium">Şifrə</label>
                            <input type="password"
                                   id="smtp_password"
                                   name="smtp_password"
                                   class="form-control @error('smtp_password') is-invalid @enderror"
                                   placeholder="Dəyişdirmək istəmirsinizsə boş buraxın">
                            @error('smtp_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($settings['smtp_username'])
                                <div class="form-text text-success"><i class="bi bi-lock-fill me-1"></i>Şifrə artıq saxlanılıb. Yalnız dəyişdirmək istəyirsinizsə doldurun.</div>
                            @endif
                        </div>

                        <div class="col-sm-8">
                            <label for="smtp_from_address" class="form-label fw-medium">Göndərən e-poçt ünvanı</label>
                            <input type="email"
                                   id="smtp_from_address"
                                   name="smtp_from_address"
                                   class="form-control @error('smtp_from_address') is-invalid @enderror"
                                   value="{{ old('smtp_from_address', $settings['smtp_from_address']) }}"
                                   placeholder="noreply@example.com"
                                   required>
                            @error('smtp_from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label for="smtp_from_name" class="form-label fw-medium">Göndərən adı</label>
                            <input type="text"
                                   id="smtp_from_name"
                                   name="smtp_from_name"
                                   class="form-control @error('smtp_from_name') is-invalid @enderror"
                                   value="{{ old('smtp_from_name', $settings['smtp_from_name']) }}"
                                   placeholder="Müəssisənizin adı"
                                   required>
                            @error('smtp_from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm border-start border-4 border-info">
            <div class="card-body">
                <h6 class="fw-semibold mb-2"><i class="bi bi-lightbulb me-2 text-info"></i>Gmail üçün nümunə</h6>
                <ul class="mb-0 small text-muted">
                    <li><strong>Host:</strong> smtp.gmail.com</li>
                    <li><strong>Port:</strong> 587</li>
                    <li><strong>Şifrələmə:</strong> TLS</li>
                    <li><strong>Şifrə:</strong> Gmail "App Password" (2FA aktiv olduqda)</li>
                </ul>
            </div>
        </div>

    </div>
</div>
@endsection
