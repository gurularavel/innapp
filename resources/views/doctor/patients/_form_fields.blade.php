{{--
    Reusable partial for dynamic patient form fields.
    Variables:
      $fields       — Collection of SpecialtyField (resolved for this doctor's specialty)
      $patient      — Patient model for edit mode, null for create
      $customValues — Keyed collection of PatientFieldValue by specialty_field_id (edit mode)
--}}
@php
    $patient      = $patient      ?? null;
    $customValues = $customValues ?? collect();
@endphp

@foreach($fields->where('is_active', true) as $field)
@php
    $isEdit   = $patient !== null;
    $isCore   = $field->is_core;
    $fKey     = $field->field_key;
    $inputName = $isCore ? $fKey : "custom[{$field->id}]";
    $oldKey    = $isCore ? $fKey  : "custom.{$field->id}";

    if ($isEdit && $isCore) {
        $currentVal = old($oldKey, $patient->{$fKey});
    } elseif ($isEdit && !$isCore) {
        $savedVal   = $customValues->get($field->id)?->value ?? '';
        $currentVal = old($oldKey, $savedVal);
    } else {
        $currentVal = old($oldKey, '');
    }

    $colClass = $field->col_class;
@endphp

{{-- ---- PHOTO ---- --}}
@if($field->type === 'photo')
<div class="{{ $colClass }}">
    <label class="form-label fw-medium">{{ $field->label }}</label>
    <div class="d-flex align-items-center gap-3">
        <div id="photo-preview-wrap"
             class="rounded-circle overflow-hidden bg-light d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:80px;height:80px;border:2px dashed #dee2e6;">
            @if($isEdit && $patient->photo_url)
                <img id="photo-preview" src="{{ $patient->photo_url }}" alt="" class="w-100 h-100" style="object-fit:cover;">
                <i id="photo-placeholder" class="bi bi-person fs-2 text-muted d-none"></i>
            @else
                <i id="photo-placeholder" class="bi bi-person fs-2 text-muted"></i>
                <img id="photo-preview" src="" alt="" class="d-none w-100 h-100" style="object-fit:cover;">
            @endif
        </div>
        <div>
            <input type="file" id="photo" name="photo" accept="image/*" class="d-none"
                   onchange="previewPhoto(this)">
            <button type="button" class="btn btn-sm btn-outline-secondary"
                    onclick="document.getElementById('photo').click()">
                <i class="bi bi-camera me-1"></i>Şəkil Seç
            </button>
            @if($isEdit && $patient->photo)
            <div class="mt-2">
                <div class="form-check form-check-inline">
                    <input type="checkbox" class="form-check-input" id="remove_photo"
                           name="remove_photo" value="1">
                    <label class="form-check-label text-danger small" for="remove_photo">Şəkli sil</label>
                </div>
            </div>
            @endif
            <div class="text-muted mt-1" style="font-size:.75rem;">JPG, PNG · Maks 2MB</div>
        </div>
    </div>
    @error('photo')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

{{-- ---- DATE ---- --}}
@elseif($field->type === 'date')
<div class="{{ $colClass }}">
    <label for="field_{{ $fKey }}" class="form-label fw-medium">{{ $field->label }}</label>
    <input type="date"
           class="form-control @error($oldKey) is-invalid @enderror"
           id="field_{{ $fKey }}"
           name="{{ $inputName }}"
           value="{{ $currentVal instanceof \Carbon\Carbon ? $currentVal->format('Y-m-d') : ($currentVal ?? '') }}">
    @error($oldKey)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- ---- NUMBER ---- --}}
@elseif($field->type === 'number')
<div class="{{ $colClass }}">
    <label for="field_{{ $fKey }}" class="form-label fw-medium">{{ $field->label }}</label>
    <input type="number" min="0" step="0.1"
           class="form-control @error($oldKey) is-invalid @enderror"
           id="field_{{ $fKey }}"
           name="{{ $inputName }}"
           value="{{ $currentVal ?? '' }}">
    @error($oldKey)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- ---- SELECT ---- --}}
@elseif($field->type === 'select')
<div class="{{ $colClass }}">
    <label for="field_{{ $fKey }}" class="form-label fw-medium">{{ $field->label }}</label>
    <select class="form-select @error($oldKey) is-invalid @enderror"
            id="field_{{ $fKey }}"
            name="{{ $inputName }}">
        <option value="">— Seçin —</option>
        @foreach($field->options ?? [] as $opt)
            <option value="{{ $opt['value'] }}"
                {{ ($currentVal ?? '') == $opt['value'] ? 'selected' : '' }}>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </select>
    @error($oldKey)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- ---- TEXTAREA ---- --}}
@elseif($field->type === 'textarea')
<div class="{{ $colClass }}">
    <label for="field_{{ $fKey }}" class="form-label fw-medium">{{ $field->label }}</label>
    <textarea class="form-control @error($oldKey) is-invalid @enderror"
              id="field_{{ $fKey }}"
              name="{{ $inputName }}"
              rows="4"
              placeholder="{{ $field->label }}…">{{ $currentVal ?? '' }}</textarea>
    @error($oldKey)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- ---- FILE ---- --}}
@elseif($field->type === 'file')
@php $fileInputName = "custom_file_{$field->id}"; @endphp
<div class="{{ $colClass }}">
    <label class="form-label fw-medium">{{ $field->label }}</label>
    @if($isEdit && $currentVal)
    <div class="d-flex align-items-center gap-2 mb-2 p-2 bg-light rounded border">
        @php
            $ext = strtolower(pathinfo($currentVal, PATHINFO_EXTENSION));
            $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
            $icon  = $isImg ? 'bi-image' : (in_array($ext, ['pdf']) ? 'bi-file-pdf' : 'bi-file-earmark');
        @endphp
        <i class="bi {{ $icon }} text-secondary fs-5"></i>
        <a href="{{ asset('storage/' . $currentVal) }}" target="_blank"
           class="text-decoration-none small flex-grow-1 text-truncate">
            {{ basename($currentVal) }}
        </a>
        <div class="form-check mb-0 flex-shrink-0">
            <input type="checkbox" class="form-check-input" id="remove_cf_{{ $field->id }}"
                   name="remove_custom_file[{{ $field->id }}]" value="1">
            <label class="form-check-label text-danger small" for="remove_cf_{{ $field->id }}">Sil</label>
        </div>
    </div>
    @endif
    <input type="file"
           class="form-control @error($fileInputName) is-invalid @enderror"
           name="{{ $fileInputName }}"
           accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt">
    <div class="form-text">JPG, PNG, PDF, Word, Excel · Maks 10MB</div>
    @error($fileInputName)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- ---- TEXT (default) ---- --}}
@else
<div class="{{ $colClass }}">
    <label for="field_{{ $fKey }}" class="form-label fw-medium">{{ $field->label }}</label>
    <input type="text"
           class="form-control @error($oldKey) is-invalid @enderror"
           id="field_{{ $fKey }}"
           name="{{ $inputName }}"
           value="{{ $currentVal ?? '' }}">
    @error($oldKey)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
@endif

@endforeach
