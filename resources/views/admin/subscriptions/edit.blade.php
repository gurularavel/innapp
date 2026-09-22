@extends('layouts.admin')

@section('title', 'Abunəliyi Redaktə Et')
@section('page-title', 'Abunəliyi Redaktə Et')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    {{ $subscription->doctor?->full_name ?? '—' }}
                    <span class="text-muted fw-normal">— {{ $subscription->clinic?->name ?? 'Müəssisəsiz' }}</span>
                </h6>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <div class="alert alert-light border small mb-4">
                    Müəssisədəki aktiv hesab sayı: <strong>{{ $subscription->used_seats }}</strong> —
                    yer sayı bundan az ola bilməz.
                </div>

                <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="package_id" class="form-label fw-medium">Paket <span class="text-danger">*</span></label>
                        <select class="form-select @error('package_id') is-invalid @enderror"
                                id="package_id" name="package_id" required>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}"
                                    {{ old('package_id', $subscription->package_id) == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} — {{ number_format($package->price_per_seat, 2) }} ₼ / əməkdaş
                                    ({{ $package->duration_days }} gün, müştəri limiti: {{ $package->patient_limit ?? '∞' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Müştəri limiti paketdən gəlir — limiti dəyişmək üçün paketi dəyişin.</div>
                        @error('package_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="starts_at" class="form-label fw-medium">Başlanğıc Tarixi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('starts_at') is-invalid @enderror"
                                   id="starts_at" name="starts_at"
                                   value="{{ old('starts_at', $subscription->starts_at->format('Y-m-d')) }}" required>
                            @error('starts_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="expires_at" class="form-label fw-medium">Bitmə Tarixi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('expires_at') is-invalid @enderror"
                                   id="expires_at" name="expires_at"
                                   value="{{ old('expires_at', $subscription->expires_at->format('Y-m-d')) }}" required>
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary js-extend" data-days="30">+1 ay</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary js-extend" data-days="90">+3 ay</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary js-extend" data-days="180">+6 ay</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary js-extend" data-days="365">+1 il</button>
                            </div>
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="seats" class="form-label fw-medium">Yer sayı <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="500" class="form-control @error('seats') is-invalid @enderror"
                                   id="seats" name="seats" value="{{ old('seats', $subscription->seats) }}" required>
                            <div class="form-text small">Hazırda istifadə: {{ $subscription->used_seats }}</div>
                            @error('seats')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label for="patients_used" class="form-label fw-medium">İstifadə olunmuş müştəri sayı <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control @error('patients_used') is-invalid @enderror"
                                   id="patients_used" name="patients_used"
                                   value="{{ old('patients_used', $subscription->patients_used) }}" required>
                            <div class="form-text small">Paket limiti: {{ $subscription->package->patient_limit ?? '∞' }}</div>
                            @error('patients_used')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-7 d-flex align-items-center">
                            <div class="form-check form-switch mt-4">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $subscription->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Aktiv abunəlik
                                    <span class="d-block text-muted small">
                                        Aktiv edilsə, müəssisənin digər abunəlikləri deaktiv olunacaq.
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda saxla
                        </button>
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script @cspNonce>
document.querySelectorAll('.js-extend').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById('expires_at');
        var today = new Date();
        today.setHours(12, 0, 0, 0);
        // An expired subscription restarts from today, a live one keeps its tail.
        var base = input.value ? new Date(input.value + 'T12:00:00') : today;
        if (base < today) {
            base = today;
        }
        base.setDate(base.getDate() + parseInt(this.dataset.days, 10));
        input.value = base.toISOString().slice(0, 10);
    });
});
</script>
@endpush
