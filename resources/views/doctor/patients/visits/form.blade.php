@extends('layouts.doctor')

@section('title', isset($visit) ? 'Ziyarəti Düzəlt' : 'Yeni Ziyarət')
@section('page-title', isset($visit) ? 'Ziyarəti Düzəlt' : 'Yeni Ziyarət Əlavə Et')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    {{ $patient->full_name }}
                    — {{ isset($visit) ? 'Ziyarəti Düzəlt' : 'Yeni Ziyarət' }}
                </h6>
                <a href="{{ route('panel.patients.show', $patient) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <form method="POST"
                      action="{{ isset($visit)
                            ? route('panel.patients.visits.update', [$patient, $visit])
                            : route('panel.patients.visits.store', $patient) }}"
                      enctype="multipart/form-data">
                    @csrf
                    @if(isset($visit)) @method('PATCH') @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="visited_at" class="form-label fw-medium">
                                Tarix və Saat <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local"
                                   class="form-control @error('visited_at') is-invalid @enderror"
                                   id="visited_at" name="visited_at"
                                   value="{{ old('visited_at', isset($visit) ? $visit->visited_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                                   required>
                            @error('visited_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="title-select" class="form-label fw-medium">Xidmət Növü</label>
                            @php $currentTitle = old('title', $visit->title ?? ''); @endphp
                            <select class="form-select @error('title') is-invalid @enderror"
                                    id="title-select">
                                <option value="">— Seçin (ixtiyari) —</option>
                                @foreach($treatmentTypes as $type)
                                    <option value="{{ $type->name }}"
                                            {{ $currentTitle === $type->name ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                                <option value="__add_new__" style="color:#0d6efd;font-weight:500;">
                                    + Yeni növ əlavə et
                                </option>
                            </select>
                            {{-- hidden input that actually submits the value --}}
                            <input type="hidden" id="title-hidden" name="title" value="{{ $currentTitle }}">
                            @error('title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label fw-medium">Qeydlər</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="4"
                                      placeholder="Görülən işlər, diaqnoz, tövsiyələr...">{{ old('notes', $visit->notes ?? '') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Existing files (edit mode) --}}
                        @if(isset($visit) && $visit->files->isNotEmpty())
                        <div class="col-12">
                            <label class="form-label fw-medium">Mövcud Fayllar</label>
                            <div class="row g-2" id="existing-files">
                                @foreach($visit->files as $file)
                                <div class="col-6 col-md-3" id="file-{{ $file->id }}">
                                    <div class="border rounded p-1 position-relative text-center" style="background:#f8f9fa;">
                                        @if($file->is_image)
                                            <img src="{{ $file->url }}" alt="{{ $file->original_name }}"
                                                 class="img-fluid rounded" style="max-height:80px;object-fit:cover;width:100%;cursor:pointer;"
                                                 onclick="window.open('{{ $file->url }}','_blank')">
                                        @else
                                            <div class="py-2">
                                                <i class="bi bi-file-earmark-pdf fs-3 text-danger"></i>
                                                <div class="small text-truncate mt-1" style="font-size:.7rem;">{{ $file->original_name }}</div>
                                            </div>
                                        @endif
                                        <button type="button"
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0"
                                                style="width:20px;height:20px;font-size:.65rem;line-height:1;"
                                                onclick="deleteFile({{ $file->id }}, {{ $patient->id }})">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label fw-medium">
                                {{ isset($visit) ? 'Yeni Fayllar Əlavə Et' : 'Şəkillər / Fayllar' }}
                            </label>
                            <div id="drop-area" class="border rounded-3 p-4 text-center"
                                 style="border-style:dashed!important;cursor:pointer;background:#fafbfc;">
                                <i class="bi bi-cloud-arrow-up fs-2 text-muted mb-2 d-block"></i>
                                <div class="text-muted small">Şəkilləri bura sürükləyin və ya klikləyin</div>
                                <div class="text-muted" style="font-size:.75rem;">JPG, PNG, GIF, PDF · Maks 5MB / fayl</div>
                                <input type="file" id="files" name="files[]" multiple
                                       accept="image/*,.pdf" class="d-none">
                            </div>
                            <div id="file-preview" class="row g-2 mt-2"></div>
                            @error('files.*')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                        </button>
                        <a href="{{ route('panel.patients.show', $patient) }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Yeni xidmət növü --}}
<div class="modal fade" id="addTreatmentTypeModal" tabindex="-1" aria-labelledby="addTreatmentTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold" id="addTreatmentTypeModalLabel">
                    <i class="bi bi-plus-circle me-1 text-primary"></i>Yeni Xidmət Növü
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-error" class="alert alert-danger d-none"></div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Ad <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="modal-name" placeholder="Xidmət adı">
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-medium">Qiymət (₼)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="modal-price" placeholder="İxtiyari">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-medium">Müddət (dəq)</label>
                        <input type="number" min="5" step="5" class="form-control" id="modal-duration" value="30">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-medium">Rəng</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" class="form-control form-control-color" id="modal-color" value="#3788d8" style="width:50px;height:38px;">
                        <span class="text-muted small">Təqvimdə göstəriləcək rəng</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Ləğv et</button>
                <button type="button" class="btn btn-primary" id="modal-save-btn">
                    <span id="modal-save-text"><i class="bi bi-check-lg me-1"></i>Əlavə et</span>
                    <span id="modal-save-spinner" class="d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span>Gözləyin...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Tom Select: Xidmət növü ───────────────────────────────────────────────────
const titleTS = new TomSelect('#title-select', {
    create: false,
    allowEmptyOption: true,
    onDropdownOpen: function() { const s = this; setTimeout(() => s.setTextboxValue(''), 0); },
    render: {
        option: function(data, escape) {
            if (data.value === '__add_new__')
                return `<div class="option" style="color:#0d6efd;font-weight:500;">${escape(data.text)}</div>`;
            return `<div class="option">${escape(data.text)}</div>`;
        }
    },
    onChange: function(value) {
        if (value === '__add_new__') {
            this.setValue(this._prev ?? '', true);
            new bootstrap.Modal(document.getElementById('addTreatmentTypeModal')).show();
            return;
        }
        this._prev = value;
        document.getElementById('title-hidden').value = value;
    }
});
titleTS._prev = titleTS.getValue();
document.getElementById('title-hidden').value = titleTS.getValue();

// ── Modal: add new treatment type via AJAX ──────────────────────────────────
document.getElementById('modal-save-btn').addEventListener('click', function () {
    const name     = document.getElementById('modal-name').value.trim();
    const price    = document.getElementById('modal-price').value;
    const duration = document.getElementById('modal-duration').value;
    const color    = document.getElementById('modal-color').value;
    const errorBox = document.getElementById('modal-error');

    errorBox.classList.add('d-none');
    errorBox.textContent = '';

    if (!name) {
        errorBox.textContent = 'Ad mütləq doldurulmalıdır.';
        errorBox.classList.remove('d-none');
        document.getElementById('modal-name').focus();
        return;
    }

    const saveText    = document.getElementById('modal-save-text');
    const saveSpinner = document.getElementById('modal-save-spinner');
    saveText.classList.add('d-none');
    saveSpinner.classList.remove('d-none');
    this.disabled = true;

    fetch('{{ route('panel.treatment-types.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        },
        body: JSON.stringify({
            name,
            price: price || null,
            duration_minutes: duration || 30,
            color: color || '#3788d8',
        }),
    })
    .then(async r => {
        const data = await r.json();
        if (!r.ok) {
            const msg = data.errors?.name?.[0] || data.message || 'Xəta baş verdi.';
            throw new Error(msg);
        }
        return data;
    })
    .then(type => {
        titleTS.addOption({ value: type.name, text: type.name });
        titleTS.setValue(type.name); // onChange fires → title-hidden updated

        // Reset modal fields & close
        document.getElementById('modal-name').value     = '';
        document.getElementById('modal-price').value    = '';
        document.getElementById('modal-duration').value = '30';
        document.getElementById('modal-color').value    = '#3788d8';
        bootstrap.Modal.getInstance(document.getElementById('addTreatmentTypeModal')).hide();
    })
    .catch(err => {
        errorBox.textContent = err.message;
        errorBox.classList.remove('d-none');
    })
    .finally(() => {
        saveText.classList.remove('d-none');
        saveSpinner.classList.add('d-none');
        document.getElementById('modal-save-btn').disabled = false;
    });
});

// ── File drag-and-drop ──────────────────────────────────────────────────────
(function () {
    const dropArea   = document.getElementById('drop-area');
    const fileInput  = document.getElementById('files');
    const previewBox = document.getElementById('file-preview');
    let selectedFiles = [];

    dropArea.addEventListener('click', () => fileInput.click());

    dropArea.addEventListener('dragover', e => {
        e.preventDefault();
        dropArea.classList.add('border-primary');
    });
    dropArea.addEventListener('dragleave', () => dropArea.classList.remove('border-primary'));
    dropArea.addEventListener('drop', e => {
        e.preventDefault();
        dropArea.classList.remove('border-primary');
        addFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', () => addFiles(fileInput.files));

    function addFiles(newFiles) {
        for (const f of newFiles) {
            selectedFiles.push(f);
        }
        renderPreviews();
        syncInput();
    }

    function renderPreviews() {
        previewBox.innerHTML = '';
        selectedFiles.forEach((f, idx) => {
            const col = document.createElement('div');
            col.className = 'col-6 col-md-3';
            const inner = document.createElement('div');
            inner.className = 'border rounded p-1 position-relative text-center';
            inner.style.background = '#f8f9fa';

            if (f.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.style.cssText = 'max-height:80px;object-fit:cover;width:100%;border-radius:4px;';
                const reader = new FileReader();
                reader.onload = e => img.src = e.target.result;
                reader.readAsDataURL(f);
                inner.appendChild(img);
            } else {
                inner.innerHTML = '<i class="bi bi-file-earmark-pdf fs-3 text-danger"></i>'
                    + `<div class="small text-truncate mt-1" style="font-size:.7rem;">${f.name}</div>`;
            }

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0 p-0';
            btn.style.cssText = 'width:20px;height:20px;font-size:.65rem;line-height:1;';
            btn.innerHTML = '<i class="bi bi-x"></i>';
            btn.onclick = () => { selectedFiles.splice(idx, 1); renderPreviews(); syncInput(); };

            inner.appendChild(btn);
            col.appendChild(inner);
            previewBox.appendChild(col);
        });
    }

    function syncInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(f => dt.items.add(f));
        fileInput.files = dt.files;
    }

    // Delete existing file via AJAX
    window.deleteFile = function (fileId, patientId) {
        if (!confirm('Bu faylı silmək istəyirsiniz?')) return;
        fetch(`/panel/patients/${patientId}/visits/files/${fileId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            }
        }).then(r => r.json()).then(data => {
            if (data.ok) {
                document.getElementById('file-' + fileId)?.remove();
            }
        });
    };
})();
</script>
@endpush
