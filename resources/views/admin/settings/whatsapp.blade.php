@extends('layouts.admin')

@section('title', 'WhatsApp Ayarları')
@section('page-title', 'WhatsApp Ayarları')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @error('whatsapp_enabled')
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ $message }}
            </div>
        @enderror

        {{-- Status summary --}}
        @php
            $isOn = $settings['whatsapp_enabled'] === '1';
        @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                     style="width:48px;height:48px;background:{{ $isOn ? '#25D366' : '#e9ecef' }};">
                    <i class="bi bi-whatsapp fs-4 {{ $isOn ? 'text-white' : 'text-secondary' }}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">
                        WhatsApp kanalı
                        <span class="badge bg-{{ $isOn ? 'success' : 'secondary' }} ms-1">
                            {{ $isOn ? 'Aktiv' : 'Deaktiv' }}
                        </span>
                    </div>
                    <div class="text-muted small">
                        @if($isOn)
                            Klinika sahibləri öz panelindən SMS, WhatsApp və ya hər ikisini seçə bilər.
                        @else
                            Deaktiv olduqda bütün bildirişlər yalnız SMS ilə göndərilir.
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-3 text-center">
                    <div>
                        <div class="fw-bold">{{ $channelUsage['sms'] ?? 0 }}</div>
                        <div class="text-muted" style="font-size:.75rem;">Klinika · SMS</div>
                    </div>
                    <div>
                        <div class="fw-bold">{{ $channelUsage['whatsapp'] ?? 0 }}</div>
                        <div class="text-muted" style="font-size:.75rem;">Yalnız WP</div>
                    </div>
                    <div>
                        <div class="fw-bold">{{ $channelUsage['both'] ?? 0 }}</div>
                        <div class="text-muted" style="font-size:.75rem;">Hər ikisi</div>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.whatsapp.save') }}">
            @csrf
            @method('PUT')

            {{-- Connection --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-plug me-2 text-success"></i>Bağlantı (WhatsApp Cloud API)</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">
                        Meta Business hesabınızdakı <strong>WhatsApp Cloud API</strong> məlumatlarını daxil edin.
                        Bu dəyərləri <code>developers.facebook.com</code> » Tətbiqiniz » WhatsApp » API Setup bölməsindən götürə bilərsiniz.
                    </p>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="whatsapp_enabled" name="whatsapp_enabled" value="1"
                               style="width:2.5em;height:1.3em;cursor:pointer;"
                               {{ old('whatsapp_enabled', $settings['whatsapp_enabled']) === '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium ms-1" for="whatsapp_enabled" style="cursor:pointer;">
                            WhatsApp kanalını aktivləşdir
                        </label>
                        <div class="text-muted small">
                            Söndürülərsə, WhatsApp seçmiş mütəxəssislərin mesajları avtomatik SMS ilə göndərilir.
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-8">
                            <label for="whatsapp_phone_number_id" class="form-label fw-medium">Phone Number ID</label>
                            <input type="text"
                                   id="whatsapp_phone_number_id"
                                   name="whatsapp_phone_number_id"
                                   class="form-control font-monospace @error('whatsapp_phone_number_id') is-invalid @enderror"
                                   value="{{ old('whatsapp_phone_number_id', $settings['whatsapp_phone_number_id']) }}"
                                   placeholder="123456789012345">
                            @error('whatsapp_phone_number_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label for="whatsapp_api_version" class="form-label fw-medium">API versiyası</label>
                            <input type="text"
                                   id="whatsapp_api_version"
                                   name="whatsapp_api_version"
                                   class="form-control @error('whatsapp_api_version') is-invalid @enderror"
                                   value="{{ old('whatsapp_api_version', $settings['whatsapp_api_version']) }}"
                                   placeholder="v21.0" required>
                            @error('whatsapp_api_version')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="whatsapp_access_token" class="form-label fw-medium">
                                Access Token
                                @if($hasToken)
                                    <span class="badge bg-success ms-1"><i class="bi bi-lock-fill me-1"></i>Saxlanılıb</span>
                                @endif
                            </label>
                            <input type="password"
                                   id="whatsapp_access_token"
                                   name="whatsapp_access_token"
                                   class="form-control font-monospace @error('whatsapp_access_token') is-invalid @enderror"
                                   autocomplete="new-password"
                                   placeholder="{{ $hasToken ? 'Dəyişmək üçün yeni token yazın' : 'EAAG...' }}">
                            <div class="form-text">
                                Token şifrələnmiş şəkildə saxlanılır və bir daha göstərilmir.
                                Boş buraxsanız mövcud token dəyişməz qalır.
                            </div>
                            @error('whatsapp_access_token')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label for="whatsapp_language_code" class="form-label fw-medium">Şablon dili</label>
                            <input type="text"
                                   id="whatsapp_language_code"
                                   name="whatsapp_language_code"
                                   class="form-control @error('whatsapp_language_code') is-invalid @enderror"
                                   value="{{ old('whatsapp_language_code', $settings['whatsapp_language_code']) }}"
                                   placeholder="az" required>
                            <div class="form-text">Meta-da təsdiqlənmiş şablonun dil kodu (az, tr, en).</div>
                            @error('whatsapp_language_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Templates --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-chat-quote me-2 text-success"></i>Təsdiqlənmiş şablonlar</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning small mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Vacib:</strong> WhatsApp qaydalarına görə söhbəti biznes başladırsa, mesaj yalnız
                        <strong>Meta tərəfindən təsdiqlənmiş şablonla</strong> göndərilə bilər.
                        Şablon adını boş buraxsanız, mesaj adi mətn kimi göndəriləcək —
                        bu isə yalnız müştəri son 24 saat ərzində sizə yazıbsa çatdırılır.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="whatsapp_appointment_template" class="form-label fw-medium">
                                <i class="bi bi-calendar-check me-1 text-primary"></i>Randevu təsdiqi — şablon adı
                            </label>
                            <input type="text"
                                   id="whatsapp_appointment_template"
                                   name="whatsapp_appointment_template"
                                   class="form-control font-monospace @error('whatsapp_appointment_template') is-invalid @enderror"
                                   value="{{ old('whatsapp_appointment_template', $settings['whatsapp_appointment_template']) }}"
                                   placeholder="randevu_tesdiqi">
                            @error('whatsapp_appointment_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="whatsapp_appointment_params" class="form-label fw-medium">Parametr sırası</label>
                            <input type="text"
                                   id="whatsapp_appointment_params"
                                   name="whatsapp_appointment_params"
                                   class="form-control font-monospace @error('whatsapp_appointment_params') is-invalid @enderror"
                                   value="{{ old('whatsapp_appointment_params', $settings['whatsapp_appointment_params']) }}"
                                   placeholder="{ad_soyad},{tarix},{saat}">
                            <div class="form-text">Şablondakı <code>&#123;&#123;1&#125;&#125;</code>, <code>&#123;&#123;2&#125;&#125;</code>… sırası ilə eyni olmalıdır.</div>
                            @error('whatsapp_appointment_params')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="whatsapp_reminder_template" class="form-label fw-medium">
                                <i class="bi bi-bell me-1 text-warning"></i>Xatırlatma — şablon adı
                            </label>
                            <input type="text"
                                   id="whatsapp_reminder_template"
                                   name="whatsapp_reminder_template"
                                   class="form-control font-monospace @error('whatsapp_reminder_template') is-invalid @enderror"
                                   value="{{ old('whatsapp_reminder_template', $settings['whatsapp_reminder_template']) }}"
                                   placeholder="randevu_xatirlatma">
                            @error('whatsapp_reminder_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="whatsapp_reminder_params" class="form-label fw-medium">Parametr sırası</label>
                            <input type="text"
                                   id="whatsapp_reminder_params"
                                   name="whatsapp_reminder_params"
                                   class="form-control font-monospace @error('whatsapp_reminder_params') is-invalid @enderror"
                                   value="{{ old('whatsapp_reminder_params', $settings['whatsapp_reminder_params']) }}"
                                   placeholder="{ad_soyad},{tarix},{saat}">
                            @error('whatsapp_reminder_params')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="whatsapp_birthday_template" class="form-label fw-medium">
                                <i class="bi bi-balloon me-1 text-danger"></i>Ad günü — şablon adı
                            </label>
                            <input type="text"
                                   id="whatsapp_birthday_template"
                                   name="whatsapp_birthday_template"
                                   class="form-control font-monospace @error('whatsapp_birthday_template') is-invalid @enderror"
                                   value="{{ old('whatsapp_birthday_template', $settings['whatsapp_birthday_template']) }}"
                                   placeholder="ad_gunu_tebriki">
                            <div class="form-text">Boş qalsa WhatsApp ilə ad günü təbriki adi mətn kimi göndərilir.</div>
                            @error('whatsapp_birthday_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="whatsapp_birthday_params" class="form-label fw-medium">Parametr sırası</label>
                            <input type="text"
                                   id="whatsapp_birthday_params"
                                   name="whatsapp_birthday_params"
                                   class="form-control font-monospace @error('whatsapp_birthday_params') is-invalid @enderror"
                                   value="{{ old('whatsapp_birthday_params', $settings['whatsapp_birthday_params']) }}"
                                   placeholder="{ad_soyad},{muessise}">
                            <div class="form-text">Ad günü üçün: <code>{ad}</code>, <code>{ad_soyad}</code>, <code>{yas}</code>, <code>{muessise}</code>, <code>{xerite}</code></div>
                            @error('whatsapp_birthday_params')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="whatsapp_holiday_template" class="form-label fw-medium">
                                <i class="bi bi-calendar-heart me-1 text-success"></i>Bayram — şablon adı
                            </label>
                            <input type="text"
                                   id="whatsapp_holiday_template"
                                   name="whatsapp_holiday_template"
                                   class="form-control font-monospace @error('whatsapp_holiday_template') is-invalid @enderror"
                                   value="{{ old('whatsapp_holiday_template', $settings['whatsapp_holiday_template']) }}"
                                   placeholder="bayram_tebriki">
                            <div class="form-text">Bütün bayramlar üçün eyni şablon işlədilir — bayramın adı parametr kimi ötürülür.</div>
                            @error('whatsapp_holiday_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="whatsapp_holiday_params" class="form-label fw-medium">Parametr sırası</label>
                            <input type="text"
                                   id="whatsapp_holiday_params"
                                   name="whatsapp_holiday_params"
                                   class="form-control font-monospace @error('whatsapp_holiday_params') is-invalid @enderror"
                                   value="{{ old('whatsapp_holiday_params', $settings['whatsapp_holiday_params']) }}"
                                   placeholder="{ad_soyad},{bayram},{muessise}">
                            <div class="form-text">Bayram üçün: <code>{ad}</code>, <code>{ad_soyad}</code>, <code>{bayram}</code>, <code>{muessise}</code>, <code>{xerite}</code></div>
                            @error('whatsapp_holiday_params')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="small">
                        <div class="fw-semibold mb-2">İstifadə oluna bilən yer tutucular</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(\App\Services\MessageBuilder::PLACEHOLDERS as $ph)
                                <code class="bg-light border rounded px-2 py-1">{{ $ph }}</code>
                            @endforeach
                        </div>
                        <div class="text-muted mt-2">
                            Mesajın mətni SMS şablonu ilə eynidir — mütəxəssisin öz şablonu, yoxdursa admin defoltu istifadə olunur.
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                </button>
                <a href="{{ route('admin.sms-logs.index', ['channel' => 'whatsapp']) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-list-ul me-1"></i>WhatsApp loqları
                </a>
            </div>
        </form>

        {{-- Test hint --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-terminal me-2 text-secondary"></i>Test</h6>
            </div>
            <div class="card-body small">
                <p class="text-muted mb-2">Ayarları yoxlamaq üçün serverdə:</p>
                <pre class="bg-dark text-light rounded p-3 mb-2"><code>php artisan whatsapp:test --phone=0551234567</code></pre>
                <pre class="bg-dark text-light rounded p-3 mb-0"><code>php artisan whatsapp:test --appointment=5 --type=reminder</code></pre>
            </div>
        </div>

    </div>
</div>
@endsection
