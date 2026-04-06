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
                            <label for="title" class="form-label fw-medium">Müalicə / Başlıq</label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title"
                                   value="{{ old('title', $visit->title ?? '') }}"
                                   placeholder="Məs: Diş çəkilməsi, Rentgen, Ağartma...">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
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
@endsection

@push('scripts')
<script>
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
