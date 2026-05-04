@extends('layouts.admin')

@section('title', 'İxtisası Düzəlt')
@section('page-title', 'İxtisası Düzəlt')

@section('content')
<div class="row g-4">

    {{-- Basic info + core fields --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">{{ $specialty->name }} — Düzəliş</h6>
                <a href="{{ route('admin.specialties.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.specialties.update', $specialty) }}">
                    @csrf
                    @method('PATCH')

                    {{-- Name --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-medium">Ad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $specialty->name) }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Active --}}
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="is_active"
                                   name="is_active" value="1"
                                   {{ old('is_active', $specialty->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-medium" for="is_active">Aktiv ixtisas</label>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Core fields config --}}
                    <div class="mb-3">
                        <h6 class="fw-semibold mb-1">Müştəri formu — daxili sahələr</h6>
                        <p class="text-muted small mb-3">Bu ixtisas üçün müştəri qeydiyyatında hansı sahələrin görünəcəyini seçin.</p>

                        <div class="list-group list-group-flush border rounded">
                            @foreach($fields->where('is_core', true) as $field)
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="form-check form-switch mb-0 flex-shrink-0">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               id="cf_{{ $field->field_key }}"
                                               name="core_fields[{{ $field->field_key }}][is_active]"
                                               value="1"
                                               {{ old("core_fields.{$field->field_key}.is_active", $field->is_active) ? 'checked' : '' }}>
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="text"
                                               class="form-control form-control-sm border-0 bg-transparent p-0 fw-medium"
                                               style="width:auto;min-width:160px;"
                                               name="core_fields[{{ $field->field_key }}][label]"
                                               value="{{ old("core_fields.{$field->field_key}.label", $field->label) }}">
                                    </div>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary small">{{ $field->type }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                        </button>
                        <a href="{{ route('admin.specialties.index') }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Custom fields --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Xüsusi sahələr</h6>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFieldModal">
                    <i class="bi bi-plus-lg me-1"></i>Yeni sahə
                </button>
            </div>

            @php $customFields = $fields->where('is_core', false); @endphp

            @if($customFields->isEmpty())
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-sliders fs-2 d-block mb-2 opacity-25"></i>
                <div class="small">Xüsusi sahə yoxdur</div>
            </div>
            @else
            <div class="list-group list-group-flush">
                @foreach($customFields as $cf)
                <div class="list-group-item px-3 py-2">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-medium flex-grow-1">{{ $cf->label }}</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary small">{{ $cf->type }}</span>
                        @if($cf->is_active)
                            <span class="badge bg-success bg-opacity-10 text-success small">Aktiv</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger small">Passiv</span>
                        @endif
                        <button class="btn btn-sm btn-link p-0 text-primary"
                                title="Düzəlt"
                                data-bs-toggle="modal"
                                data-bs-target="#editFieldModal{{ $cf->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST"
                              action="{{ route('admin.specialties.fields.remove', [$specialty, $cf]) }}"
                              class="d-inline"
                              onsubmit="return confirm('Bu sahəni silmək istəyirsiniz?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-link p-0 text-danger" title="Sil">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                    @if($cf->type === 'select' && $cf->options)
                        <div class="text-muted" style="font-size:.75rem;">
                            {{ collect($cf->options)->pluck('label')->join(', ') }}
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>

{{-- Add Field Modal --}}
<div class="modal fade" id="addFieldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Yeni xüsusi sahə</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.specialties.fields.add', $specialty) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Sahə adı <span class="text-danger">*</span></label>
                        <input type="text" name="label" class="form-control @error('label') is-invalid @enderror"
                               placeholder="məs. Saç növü, Dəri tipi…"
                               value="{{ old('label') }}" required autofocus>
                        @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Tip <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" id="addFieldType" onchange="toggleOptionsAdd(this)">
                            <option value="text">Mətn</option>
                            <option value="number">Nömrə</option>
                            <option value="date">Tarix</option>
                            <option value="select">Seçim (açılan siyahı)</option>
                            <option value="textarea">Uzun mətn</option>
                            <option value="file">Fayl / Şəkil yükləmə</option>
                        </select>
                    </div>

                    <div id="addOptionsSection" class="mb-3 d-none">
                        <label class="form-label fw-medium">Seçim variantları</label>
                        <textarea name="options_text" class="form-control" rows="5"
                                  placeholder="Hər sətirdə bir variant yazın:&#10;Düz&#10;Qıvrım&#10;Dalğalı"></textarea>
                        <div class="form-text">Hər variant ayrı sətirdə</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Ləğv et</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Əlavə et
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Field Modals --}}
@foreach($fields->where('is_core', false) as $cf)
<div class="modal fade" id="editFieldModal{{ $cf->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Sahəni düzəlt</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.specialties.fields.update', [$specialty, $cf]) }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Sahə adı <span class="text-danger">*</span></label>
                        <input type="text" name="label" class="form-control"
                               value="{{ $cf->label }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Tip</label>
                        <select name="type" class="form-select"
                                id="editFieldType{{ $cf->id }}"
                                onchange="toggleOptionsEdit(this, {{ $cf->id }})">
                            @foreach(['text' => 'Mətn', 'number' => 'Nömrə', 'date' => 'Tarix', 'select' => 'Seçim (açılan siyahı)', 'textarea' => 'Uzun mətn', 'file' => 'Fayl / Şəkil yükləmə'] as $val => $lbl)
                                <option value="{{ $val }}" {{ $cf->type === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="editOptionsSection{{ $cf->id }}" class="mb-3 {{ $cf->type !== 'select' ? 'd-none' : '' }}">
                        <label class="form-label fw-medium">Seçim variantları</label>
                        <textarea name="options_text" class="form-control" rows="5"
                                  placeholder="Hər sətirdə bir variant">{{ $cf->options ? collect($cf->options)->pluck('label')->join("\n") : '' }}</textarea>
                        <div class="form-text">Hər variant ayrı sətirdə</div>
                    </div>

                    <div class="form-check form-switch mt-2">
                        <input type="checkbox" class="form-check-input" name="is_active" value="1"
                               id="editActive{{ $cf->id }}" {{ $cf->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="editActive{{ $cf->id }}">Aktiv</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Ləğv et</button>
                    <button type="submit" class="btn btn-primary">Yadda Saxla</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('scripts')
<script>
function toggleOptionsAdd(sel) {
    document.getElementById('addOptionsSection').classList.toggle('d-none', sel.value !== 'select');
}
function toggleOptionsEdit(sel, id) {
    document.getElementById('editOptionsSection' + id).classList.toggle('d-none', sel.value !== 'select');
}
// Re-open modal if validation errors returned with modal data
@if($errors->any() && old('label'))
document.addEventListener('DOMContentLoaded', () => {
    new bootstrap.Modal(document.getElementById('addFieldModal')).show();
});
@endif
</script>
@endpush
