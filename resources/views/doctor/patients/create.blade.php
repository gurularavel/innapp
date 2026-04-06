@extends('layouts.doctor')

@section('title', 'Yeni Müştəri')
@section('page-title', 'Yeni Müştəri')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Yeni Müştəri Əlavə Et</h6>
                <a href="{{ route('panel.patients.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('panel.patients.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        {{-- Photo Upload --}}
                        <div class="col-12">
                            <label class="form-label fw-medium">Profil Şəkli</label>
                            <div class="d-flex align-items-center gap-3">
                                <div id="photo-preview-wrap" class="rounded-circle overflow-hidden bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:80px;height:80px;border:2px dashed #dee2e6;">
                                    <i id="photo-placeholder" class="bi bi-person fs-2 text-muted"></i>
                                    <img id="photo-preview" src="" alt="" class="d-none w-100 h-100" style="object-fit:cover;">
                                </div>
                                <div>
                                    <input type="file" id="photo" name="photo" accept="image/*" class="d-none"
                                           onchange="previewPhoto(this)">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="document.getElementById('photo').click()">
                                        <i class="bi bi-camera me-1"></i>Şəkil Seç
                                    </button>
                                    <div class="text-muted mt-1" style="font-size:.75rem;">JPG, PNG · Maks 2MB</div>
                                </div>
                            </div>
                            @error('photo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label fw-medium">Ad <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required autofocus
                                       autocomplete="off">
                                <div id="name-suggestions" class="suggestion-dropdown d-none"></div>
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="surname" class="form-label fw-medium">Soyad <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                       id="surname" name="surname" value="{{ old('surname') }}" required
                                       autocomplete="off">
                                <div id="surname-suggestions" class="suggestion-dropdown d-none"></div>
                            </div>
                            @error('surname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-medium">Telefon <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}"
                                       autocomplete="off">
                                <div id="phone-suggestions" class="suggestion-dropdown d-none"></div>
                            </div>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="birth_date" class="form-label fw-medium">Doğum Tarixi</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                                   id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                            @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="gender" class="form-label fw-medium">Cins</label>
                            <select class="form-select @error('gender') is-invalid @enderror"
                                    id="gender" name="gender">
                                <option value="">— Seçin —</option>
                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Kişi</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Qadın</option>
                                <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Digər</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="weight" class="form-label fw-medium">Çəki (kg)</label>
                            <input type="number" min="0" max="999" step="0.1"
                                   class="form-control @error('weight') is-invalid @enderror"
                                   id="weight" name="weight" value="{{ old('weight') }}" placeholder="72.5">
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="blood_type" class="form-label fw-medium">Qan Qrupu</label>
                            <select class="form-select @error('blood_type') is-invalid @enderror"
                                    id="blood_type" name="blood_type">
                                <option value="">— Seçin —</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                                    <option value="{{ $bt }}" {{ old('blood_type') === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                @endforeach
                            </select>
                            @error('blood_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="marital_status" class="form-label fw-medium">Ailə Vəziyyəti</label>
                            <select class="form-select @error('marital_status') is-invalid @enderror"
                                    id="marital_status" name="marital_status">
                                <option value="">— Seçin —</option>
                                <option value="single"   {{ old('marital_status') === 'single'   ? 'selected' : '' }}>Subay</option>
                                <option value="married"  {{ old('marital_status') === 'married'  ? 'selected' : '' }}>Evli</option>
                                <option value="divorced" {{ old('marital_status') === 'divorced' ? 'selected' : '' }}>Boşanmış</option>
                                <option value="widowed"  {{ old('marital_status') === 'widowed'  ? 'selected' : '' }}>Dul</option>
                            </select>
                            @error('marital_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label fw-medium">Qeydlər</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="4"
                                      placeholder="Müştəri haqqında əlavə məlumat...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                        </button>
                        <a href="{{ route('panel.patients.index') }}" class="btn btn-outline-secondary">Ləğv et</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Duplicate Patient Modal --}}
<div class="modal fade" id="duplicateModal" tabindex="-1" aria-labelledby="duplicateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning bg-opacity-10 border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-warning bg-opacity-25 rounded-circle p-2">
                        <i class="bi bi-person-exclamation text-warning fs-5"></i>
                    </div>
                    <h6 class="modal-title fw-bold mb-0" id="duplicateModalLabel">Müştəri artıq mövcuddur</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="text-muted mb-2">Bu müştəri bazada artıq qeydə alınıb:</p>
                <div class="bg-light rounded p-3 mb-3">
                    <div class="fw-semibold" id="dup-name"></div>
                    <div class="text-muted small mt-1"><i class="bi bi-telephone me-1"></i><span id="dup-phone"></span></div>
                </div>
                <p class="mb-0">Bu müştəri üçün <strong>randevu əlavə etmək</strong> istəyirsiniz?</p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Bağla</button>
                <a href="#" id="dup-appointment-btn" class="btn btn-primary">
                    <i class="bi bi-calendar-plus me-1"></i>Randevu Əlavə Et
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.suggestion-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050;
    background: #fff;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 .375rem .375rem;
    box-shadow: 0 4px 12px rgba(0,0,0,.1);
    max-height: 220px;
    overflow-y: auto;
}
.suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    font-size: .875rem;
}
.suggestion-item:last-child { border-bottom: none; }
.suggestion-item:hover { background: #f0f6ff; }
.suggestion-item .patient-name { font-weight: 600; }
.suggestion-item .patient-phone { color: #6c757d; font-size: .8rem; margin-left: 6px; }
.suggestion-item .patient-dob  { color: #adb5bd; font-size: .75rem; }
</style>
@endpush

@push('scripts')
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('photo-preview').src = e.target.result;
            document.getElementById('photo-preview').classList.remove('d-none');
            document.getElementById('photo-placeholder').classList.add('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

(function () {
    const searchUrl      = '{{ route('panel.patients.search') }}';
    const appointmentUrl = '{{ route('panel.appointments.create') }}';
    const modal          = new bootstrap.Modal(document.getElementById('duplicateModal'));
    let timer = null;

    function showDuplicateModal(patient) {
        document.getElementById('dup-name').textContent  = patient.name + ' ' + patient.surname;
        document.getElementById('dup-phone').textContent = patient.phone || '—';
        document.getElementById('dup-appointment-btn').href = appointmentUrl + '?patient_id=' + patient.id;
        closeAll();
        modal.show();
    }

    function buildDropdown(patients, dropdownEl) {
        dropdownEl.innerHTML = '';
        if (!patients.length) { dropdownEl.classList.add('d-none'); return; }

        patients.forEach(p => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            const dob = p.birth_date ? `<span class="patient-dob ms-1">(${p.birth_date})</span>` : '';
            item.innerHTML = `<span class="patient-name">${p.name} ${p.surname}</span>`
                           + `<span class="patient-phone"><i class="bi bi-telephone me-1"></i>${p.phone || '—'}</span>${dob}`;
            item.addEventListener('mousedown', e => { e.preventDefault(); showDuplicateModal(p); });
            dropdownEl.appendChild(item);
        });

        dropdownEl.classList.remove('d-none');
    }

    function closeAll() {
        document.querySelectorAll('.suggestion-dropdown').forEach(d => d.classList.add('d-none'));
    }

    function attachAutocomplete(inputId, dropdownId) {
        const input    = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);

        input.addEventListener('input', function () {
            clearTimeout(timer);
            const q = this.value.trim();
            if (q.length < 2) { dropdown.classList.add('d-none'); return; }

            timer = setTimeout(() => {
                fetch(`${searchUrl}?q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(data => buildDropdown(data, dropdown));
            }, 300);
        });

        input.addEventListener('blur',  () => setTimeout(closeAll, 150));
        input.addEventListener('focus', function () {
            if (this.value.trim().length >= 2 && dropdown.children.length) {
                dropdown.classList.remove('d-none');
            }
        });
    }

    attachAutocomplete('name',    'name-suggestions');
    attachAutocomplete('surname', 'surname-suggestions');
    attachAutocomplete('phone',   'phone-suggestions');

    document.addEventListener('click', e => {
        if (!e.target.closest('.position-relative')) closeAll();
    });

    @if(session('duplicate_patient'))
    // Server-side duplicate — show modal on page load
    (function () {
        const dp = @json(session('duplicate_patient'));
        document.getElementById('dup-name').textContent  = dp.name;
        document.getElementById('dup-phone').textContent = dp.phone || '—';
        document.getElementById('dup-appointment-btn').href = dp.url;
        modal.show();
    })();
    @endif
})();
</script>
@endpush
