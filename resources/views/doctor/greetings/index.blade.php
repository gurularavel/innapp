@extends('layouts.doctor')

@section('title', 'Təbrik Mesajları')
@section('page-title', 'Təbrik Mesajları')

@section('content')
@php
    $channelLabel = match($clinic->notify_channel ?? 'sms') {
        'whatsapp' => 'WhatsApp',
        'both'     => 'SMS + WhatsApp',
        default    => 'SMS',
    };
@endphp

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="alert alert-light border small d-flex gap-2">
            <i class="bi bi-info-circle text-info mt-1"></i>
            <div>
                Təbriklər hər gün saat <strong>{{ sprintf('%02d:00', $sendHour) }}</strong> radələrində,
                bildiriş kanalınızla (<strong>{{ $channelLabel }}</strong>) avtomatik göndərilir.
                Kanalı <a href="{{ route('panel.sms-templates.index') }}">Bildiriş Ayarları</a> bölməsindən dəyişə bilərsiniz.
                Şablonu boş buraxsanız sistem defolt mətni işlədəcək.
            </div>
        </div>

        @unless($canEdit)
            <div class="alert alert-secondary small py-2 px-3">
                <i class="bi bi-lock me-1"></i>
                Bu ayarları yalnız klinika sahibi dəyişə bilər.
            </div>
        @endunless

        <form method="POST" action="{{ route('panel.greetings.save') }}">
            @csrf
            @method('PUT')

            {{-- Birthday --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-balloon me-2 text-danger"></i>Ad Günü Təbriki</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="birthday_greetings_enabled" name="birthday_greetings_enabled" value="1"
                               style="width:2.4em;height:1.25em;cursor:pointer;"
                               {{ old('birthday_greetings_enabled', $clinic->birthday_greetings_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium ms-1" for="birthday_greetings_enabled" style="cursor:pointer;">
                            Müştərilərə ad günü təbriki göndər
                        </label>
                    </div>

                    <p class="text-muted small mb-2">
                        Doğum tarixi bugünə düşən hər müştəriyə bir dəfə göndərilir.
                        Doğum tarixi və ya telefon nömrəsi qeyd olunmayan müştərilərə mesaj getmir.
                    </p>

                    <div class="mb-2 d-flex flex-wrap gap-1">
                        @foreach(\App\Services\MessageBuilder::BIRTHDAY_PLACEHOLDERS as $ph)
                            <button type="button" class="btn btn-outline-secondary btn-sm placeholder-btn"
                                    data-target="sms_birthday_template" data-placeholder="{{ $ph }}">{{ $ph }}</button>
                        @endforeach
                    </div>

                    <textarea id="sms_birthday_template"
                              name="sms_birthday_template"
                              class="form-control font-monospace @error('sms_birthday_template') is-invalid @enderror"
                              rows="3" maxlength="160"
                              placeholder="{{ $defaults['birthday'] }}">{{ old('sms_birthday_template', $clinic->sms_birthday_template) }}</textarea>
                    <div class="d-flex justify-content-between mt-1">
                        @error('sms_birthday_template')
                            <div class="text-danger small">{{ $message }}</div>
                        @else
                            <div class="form-text">Boş buraxsanız yuxarıdakı defolt mətn göndəriləcək.</div>
                        @enderror
                        <small class="text-muted"><span data-counter-for="sms_birthday_template">0</span>/160</small>
                    </div>
                </div>
            </div>

            {{-- Holidays --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-calendar-heart me-2 text-success"></i>Bayram Təbrikləri</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="holiday_greetings_enabled" name="holiday_greetings_enabled" value="1"
                               style="width:2.4em;height:1.25em;cursor:pointer;"
                               {{ old('holiday_greetings_enabled', $clinic->holiday_greetings_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium ms-1" for="holiday_greetings_enabled" style="cursor:pointer;">
                            Müştərilərə bayram təbriki göndər
                        </label>
                    </div>

                    <p class="text-muted small mb-3">
                        Seçdiyiniz bayramlarda telefon nömrəsi olan bütün müştərilərinizə göndərilir.
                        Hər bayram üçün ayrıca mətn yaza bilərsiniz — boş qalan mətn üçün ümumi şablon işlədilir.
                    </p>

                    @forelse($sharedHolidays as $holiday)
                        @php
                            $override = $overrides->get($holiday->id);
                            $enabled  = old("holidays.{$holiday->id}.enabled", $override?->is_enabled ?? true);
                            $custom   = old("holidays.{$holiday->id}.template", $override?->template);
                            $fallback = $holiday->template ?: $defaults['holiday'];
                            $areaId   = 'holiday_tpl_' . $holiday->id;
                        @endphp
                        <div class="border rounded-3 p-3 mb-2">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox"
                                           id="holiday_{{ $holiday->id }}"
                                           name="holidays[{{ $holiday->id }}][enabled]" value="1"
                                           style="width:1.15em;height:1.15em;cursor:pointer;"
                                           {{ $enabled ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium ms-1" for="holiday_{{ $holiday->id }}" style="cursor:pointer;">
                                        {{ $holiday->name }}
                                    </label>
                                    <span class="badge bg-light text-dark border ms-1">{{ $holiday->date_label }}</span>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#tpl_{{ $holiday->id }}">
                                    <i class="bi bi-pencil-square me-1"></i>Mətn
                                </button>
                            </div>

                            <div class="collapse {{ trim((string) $custom) !== '' ? 'show' : '' }}" id="tpl_{{ $holiday->id }}">
                                <div class="mt-3">
                                    <div class="mb-2 d-flex flex-wrap gap-1">
                                        @foreach(\App\Services\MessageBuilder::HOLIDAY_PLACEHOLDERS as $ph)
                                            <button type="button" class="btn btn-outline-secondary btn-sm placeholder-btn"
                                                    data-target="{{ $areaId }}" data-placeholder="{{ $ph }}">{{ $ph }}</button>
                                        @endforeach
                                    </div>
                                    <textarea id="{{ $areaId }}"
                                              name="holidays[{{ $holiday->id }}][template]"
                                              class="form-control form-control-sm font-monospace"
                                              rows="2" maxlength="160"
                                              placeholder="{{ $fallback }}">{{ $custom }}</textarea>
                                    <div class="d-flex justify-content-between mt-1">
                                        <div class="form-text">Boş buraxsanız: “{{ \Illuminate\Support\Str::limit($fallback, 60) }}”</div>
                                        <small class="text-muted"><span data-counter-for="{{ $areaId }}">0</span>/160</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">Hazırda sistemdə bayram təqvimi boşdur.</div>
                    @endforelse
                </div>
            </div>

            <button type="submit" class="btn btn-primary mb-4" @disabled(!$canEdit)>
                <i class="bi bi-check-lg me-1"></i>Yadda Saxla
            </button>
        </form>

        {{-- The clinic's own dates — separate CRUD, so outside the form above --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-calendar-plus me-2 text-primary"></i>Öz Tarixləriniz</h6>
                @if($canEdit)
                    <a href="{{ route('panel.greetings.holidays.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Yeni Tarix
                    </a>
                @endif
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Klinikanızın yubileyi kimi yalnız sizə aid tarixlər. Bunlar da yuxarıdakı
                    <strong>bayram təbriki</strong> açarı ilə birlikdə işləyir.
                </p>

                @forelse($ownHolidays as $holiday)
                    <div class="d-flex justify-content-between align-items-center border rounded-3 p-2 mb-2 {{ $holiday->hasPassed() ? 'opacity-50' : '' }}">
                        <div>
                            <span class="fw-medium">{{ $holiday->name }}</span>
                            <span class="badge bg-light text-dark border ms-1">{{ $holiday->date_label }}</span>
                            @unless($holiday->is_active)
                                <span class="badge bg-secondary ms-1">deaktiv</span>
                            @endunless
                            <div class="text-muted small mt-1">
                                {{ $holiday->template ? \Illuminate\Support\Str::limit($holiday->template, 70) : '— ümumi bayram şablonu —' }}
                            </div>
                        </div>
                        @if($canEdit)
                            <div class="d-flex gap-1">
                                <a href="{{ route('panel.greetings.holidays.edit', $holiday) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('panel.greetings.holidays.destroy', $holiday) }}" method="POST"
                                      onsubmit="return confirm('{{ $holiday->name }} silinsin?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-muted small">Hələ öz tarixiniz yoxdur.</div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
@include('holidays._scripts')
@endpush
