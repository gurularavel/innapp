@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label fw-medium">Ad <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="name" name="name" value="{{ old('name', $admin->name ?? '') }}" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="surname" class="form-label fw-medium">Soyad <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('surname') is-invalid @enderror"
               id="surname" name="surname" value="{{ old('surname', $admin->surname ?? '') }}" required>
        @error('surname')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label fw-medium">E-poçt <span class="text-danger">*</span></label>
        <input type="email" class="form-control @error('email') is-invalid @enderror"
               id="email" name="email" value="{{ old('email', $admin->email ?? '') }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="phone" class="form-label fw-medium">Telefon</label>
        <input type="text" class="form-control @error('phone') is-invalid @enderror"
               id="phone" name="phone" value="{{ old('phone', $admin->phone ?? '') }}">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="password" class="form-label fw-medium">
            Şifrə @if(!isset($admin))<span class="text-danger">*</span>@endif
        </label>
        <input type="password" class="form-control @error('password') is-invalid @enderror"
               id="password" name="password" {{ isset($admin) ? '' : 'required' }} autocomplete="new-password">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @isset($admin)<div class="form-text">Dəyişmək istəmirsinizsə boş buraxın.</div>@endisset
    </div>
    <div class="col-md-6">
        <label for="password_confirmation" class="form-label fw-medium">Şifrə təkrar</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
    </div>
    <div class="col-12">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $admin->is_active ?? true) ? 'checked' : '' }}
                   {{ isset($admin) && $admin->id === auth()->id() ? 'disabled' : '' }}>
            <label class="form-check-label fw-medium" for="is_active">Aktiv</label>
        </div>
        @if(isset($admin) && $admin->id === auth()->id())
            <div class="form-text text-muted">Öz hesabınızı deaktiv edə bilməzsiniz.</div>
        @endif
    </div>
</div>
