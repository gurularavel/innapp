@php
    $isEdit  = isset($staff);
    $isOwner = $isEdit && $staff->id === $clinic->owner_id;
    $current = fn ($field, $default = null) => old($field, $isEdit ? $staff->{$field} : $default);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label fw-medium">Ad <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ $current('name') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="surname" class="form-label fw-medium">Soyad <span class="text-danger">*</span></label>
        <input type="text" id="surname" name="surname"
               class="form-control @error('surname') is-invalid @enderror"
               value="{{ $current('surname') }}" required>
        @error('surname')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label fw-medium">E-poçt <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email"
               class="form-control @error('email') is-invalid @enderror"
               value="{{ $current('email') }}" required>
        <div class="form-text">Əməkdaş bu e-poçtla sistemə daxil olacaq.</div>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="phone" class="form-label fw-medium">Telefon</label>
        <input type="text" id="phone" name="phone"
               class="form-control @error('phone') is-invalid @enderror"
               value="{{ $current('phone') }}" placeholder="0551234567">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="role" class="form-label fw-medium">Rol <span class="text-danger">*</span></label>
        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror"
                {{ $isOwner ? 'disabled' : '' }} required>
            <option value="doctor" {{ $current('role', 'doctor') === 'doctor' ? 'selected' : '' }}>
                Mütəxəssis — təqvimi var, randevu qəbul edir
            </option>
            <option value="receptionist" {{ $current('role') === 'receptionist' ? 'selected' : '' }}>
                Resepsiyonist — randevu yazır, öz təqvimi yoxdur
            </option>
            <option value="owner" {{ $current('role') === 'owner' ? 'selected' : '' }}>
                Klinika sahibi — hər şeyi idarə edir
            </option>
        </select>
        @if($isOwner)
            <input type="hidden" name="role" value="owner">
            <div class="form-text">Klinika sahibinin rolu dəyişdirilə bilməz.</div>
        @endif
        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="specialty_id" class="form-label fw-medium">İxtisas</label>
        <select id="specialty_id" name="specialty_id" class="form-select @error('specialty_id') is-invalid @enderror">
            <option value="">— Seçilməyib —</option>
            @foreach($specialties as $specialty)
                <option value="{{ $specialty->id }}" {{ (string) $current('specialty_id') === (string) $specialty->id ? 'selected' : '' }}>
                    {{ $specialty->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Müştəri kartındakı dinamik sahələri ixtisas müəyyən edir.</div>
        @error('specialty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="job_title" class="form-label fw-medium">Vəzifə (sərbəst mətn)</label>
        <input type="text" id="job_title" name="job_title"
               class="form-control @error('job_title') is-invalid @enderror"
               value="{{ $current('job_title') }}" placeholder="Baş həkim, administrator...">
        @error('job_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch mb-2" id="calendar-toggle-wrap">
            <input class="form-check-input" type="checkbox" role="switch"
                   id="takes_appointments" name="takes_appointments" value="1"
                   style="width:2.5em;height:1.3em;cursor:pointer;"
                   {{ $current('takes_appointments', true) ? 'checked' : '' }}>
            <label class="form-check-label ms-1" for="takes_appointments" style="cursor:pointer;">
                Öz təqvimi olsun və randevu qəbul etsin
            </label>
        </div>
    </div>

    <div class="col-12"><hr class="my-2"></div>

    <div class="col-md-6">
        <label for="password" class="form-label fw-medium">
            Şifrə @unless($isEdit)<span class="text-danger">*</span>@endunless
        </label>
        <input type="password" id="password" name="password" autocomplete="new-password"
               class="form-control @error('password') is-invalid @enderror"
               {{ $isEdit ? '' : 'required' }}>
        @if($isEdit)<div class="form-text">Dəyişdirmək istəmirsinizsə boş buraxın.</div>@endif
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" class="form-label fw-medium">Şifrə təkrarı</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               autocomplete="new-password" class="form-control" {{ $isEdit ? '' : 'required' }}>
    </div>
</div>

@push('scripts')
<script @cspNonce>
// A receptionist never has their own calendar.
(function () {
    const role = document.getElementById('role');
    const cal  = document.getElementById('takes_appointments');
    if (!role || !cal) return;

    function sync() {
        const isReceptionist = role.value === 'receptionist';
        cal.disabled = isReceptionist;
        if (isReceptionist) cal.checked = false;
        document.getElementById('calendar-toggle-wrap').classList.toggle('opacity-50', isReceptionist);
    }
    role.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
