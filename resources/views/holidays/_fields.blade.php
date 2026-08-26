{{--
    Holiday form fields, shared by the admin's platform-wide calendar and a
    clinic's own dates. The surrounding <form> — action, method and buttons —
    belongs to the including view.
--}}
<div class="mb-3">
    <label for="name" class="form-label fw-medium">Bayramın adı <span class="text-danger">*</span></label>
    <input type="text"
           id="name"
           name="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $holiday->name) }}"
           maxlength="100"
           placeholder="Novruz bayramı"
           required>
    <div class="form-text">Mətndəki <code>{bayram}</code> yer tutucusu bu adla əvəzlənir.</div>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row g-3 mb-3">
    <div class="col-md-5">
        <label for="month" class="form-label fw-medium">Ay <span class="text-danger">*</span></label>
        <select id="month" name="month" class="form-select @error('month') is-invalid @enderror" required>
            @foreach(\App\Models\Holiday::MONTHS as $number => $label)
                <option value="{{ $number }}" {{ (int) old('month', $holiday->month) === $number ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('month')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="day" class="form-label fw-medium">Gün <span class="text-danger">*</span></label>
        <input type="number"
               id="day"
               name="day"
               class="form-control @error('day') is-invalid @enderror"
               value="{{ old('day', $holiday->day) }}"
               min="1" max="31" required>
        @error('day')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="year" class="form-label fw-medium">İl</label>
        <input type="number"
               id="year"
               name="year"
               class="form-control @error('year') is-invalid @enderror"
               value="{{ old('year', $holiday->year) }}"
               min="2020" max="2100"
               placeholder="hər il">
        <div class="form-text">Boş buraxsanız hər il təkrarlanır.</div>
        @error('year')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="template" class="form-label fw-medium">Təbrik mətni</label>
    <div class="mb-2 d-flex flex-wrap gap-1">
        @foreach(\App\Services\MessageBuilder::HOLIDAY_PLACEHOLDERS as $ph)
            <button type="button" class="btn btn-outline-secondary btn-sm placeholder-btn"
                    data-target="template" data-placeholder="{{ $ph }}">{{ $ph }}</button>
        @endforeach
    </div>
    <textarea id="template"
              name="template"
              class="form-control font-monospace @error('template') is-invalid @enderror"
              rows="3" maxlength="160"
              placeholder="Boş buraxın — ümumi bayram şablonu istifadə olunacaq">{{ old('template', $holiday->template) }}</textarea>
    <div class="d-flex justify-content-between mt-1">
        @error('template')
            <div class="text-danger small">{{ $message }}</div>
        @else
            <div class="form-text">Bu bayrama xüsusi mətn yazmaq istəmirsinizsə boş buraxın.</div>
        @enderror
        <small class="text-muted"><span data-counter-for="template">0</span>/160</small>
    </div>
</div>

<div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
           {{ old('is_active', $holiday->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label fw-medium" for="is_active">Aktivdir</label>
</div>
