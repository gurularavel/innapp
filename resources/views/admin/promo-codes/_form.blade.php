@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label for="code" class="form-label fw-medium">Promo Kod <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('code') is-invalid @enderror"
               id="code" name="code" value="{{ old('code', $promoCode->code ?? '') }}"
               style="text-transform:uppercase" placeholder="MƏS: YAY2026" required autofocus>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Yalnız hərf, rəqəm, tire və alt xətt.</div>
    </div>

    <div class="col-md-6">
        <label for="promoter_id" class="form-label fw-medium">Promotor <span class="text-danger">*</span></label>
        <select class="form-select @error('promoter_id') is-invalid @enderror" id="promoter_id" name="promoter_id" required>
            <option value="">— Seçin —</option>
            @foreach($promoters as $promoter)
                <option value="{{ $promoter->id }}"
                    {{ (string) old('promoter_id', $promoCode->promoter_id ?? '') === (string) $promoter->id ? 'selected' : '' }}>
                    {{ $promoter->full_name }} ({{ $promoter->email }})
                </option>
            @endforeach
        </select>
        @error('promoter_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if($promoters->isEmpty())
            <div class="form-text text-danger">Əvvəlcə <a href="{{ route('admin.promoters.create') }}">promotor yaradın</a>.</div>
        @endif
    </div>

    <div class="col-12"><hr class="my-1"><small class="text-muted fw-semibold">Müştəriyə endirim</small></div>

    <div class="col-md-6">
        <label for="discount_type" class="form-label fw-medium">Endirim növü <span class="text-danger">*</span></label>
        <select class="form-select" id="discount_type" name="discount_type">
            <option value="percent" {{ old('discount_type', $promoCode->discount_type ?? 'percent') === 'percent' ? 'selected' : '' }}>Faiz (%)</option>
            <option value="fixed" {{ old('discount_type', $promoCode->discount_type ?? '') === 'fixed' ? 'selected' : '' }}>Sabit məbləğ (₼)</option>
        </select>
    </div>
    <div class="col-md-6">
        <label for="discount_value" class="form-label fw-medium">Endirim dəyəri <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" class="form-control @error('discount_value') is-invalid @enderror"
               id="discount_value" name="discount_value" value="{{ old('discount_value', $promoCode->discount_value ?? '0') }}" required>
        @error('discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12"><hr class="my-1"><small class="text-muted fw-semibold">Promotor komissiyası (ilk ödənişdə)</small></div>

    <div class="col-md-6">
        <label for="commission_type" class="form-label fw-medium">Komissiya növü <span class="text-danger">*</span></label>
        <select class="form-select" id="commission_type" name="commission_type">
            <option value="percent" {{ old('commission_type', $promoCode->commission_type ?? 'percent') === 'percent' ? 'selected' : '' }}>Faiz (%)</option>
            <option value="fixed" {{ old('commission_type', $promoCode->commission_type ?? '') === 'fixed' ? 'selected' : '' }}>Sabit məbləğ (₼)</option>
        </select>
        <div class="form-text">Faiz seçilərsə, faktiki ödənilən məbləğdən hesablanır.</div>
    </div>
    <div class="col-md-6">
        <label for="commission_value" class="form-label fw-medium">Komissiya dəyəri <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" class="form-control @error('commission_value') is-invalid @enderror"
               id="commission_value" name="commission_value" value="{{ old('commission_value', $promoCode->commission_value ?? '0') }}" required>
        @error('commission_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12"><hr class="my-1"><small class="text-muted fw-semibold">Limitlər</small></div>

    <div class="col-md-6">
        <label for="max_uses" class="form-label fw-medium">Maksimum istifadə</label>
        <input type="number" min="1" class="form-control @error('max_uses') is-invalid @enderror"
               id="max_uses" name="max_uses" value="{{ old('max_uses', $promoCode->max_uses ?? '') }}" placeholder="Boş = limitsiz">
        @error('max_uses')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="expires_at" class="form-label fw-medium">Bitmə tarixi</label>
        <input type="date" class="form-control @error('expires_at') is-invalid @enderror"
               id="expires_at" name="expires_at"
               value="{{ old('expires_at', isset($promoCode->expires_at) ? $promoCode->expires_at->format('Y-m-d') : '') }}">
        @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Boş = müddətsiz.</div>
    </div>

    <div class="col-12">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $promoCode->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label fw-medium" for="is_active">Aktiv</label>
        </div>
    </div>
</div>
