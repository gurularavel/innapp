@extends('layouts.doctor')

@section('title', 'WhatsApp Bağlantısı')
@section('page-title', 'WhatsApp Bağlantısı')

@section('content')
@php
    $enabled = (bool) old('whatsapp_enabled', $clinic->whatsapp_enabled);
@endphp
<div class="row justify-content-center">
    <div class="col-lg-9">

        @error('whatsapp_enabled')
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ $message }}
            </div>
        @enderror

        {{-- Which connection is actually sending right now --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                     style="width:48px;height:48px;background:{{ $whatsappReady ? '#25D366' : '#e9ecef' }};">
                    <i class="bi bi-whatsapp fs-4 {{ $whatsappReady ? 'text-white' : 'text-secondary' }}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">
                        @if($usingOwn)
                            Öz WhatsApp nömrənizdən göndərilir
                            <span class="badge bg-success ms-1">Aktiv</span>
                        @elseif($whatsappReady)
                            Sistem nömrəsindən göndərilir
                            <span class="badge bg-info ms-1">Sistem bağlantısı</span>
                        @else
                            WhatsApp hazırda göndərmir
                            <span class="badge bg-secondary ms-1">Deaktiv</span>
                        @endif
                    </div>
                    <div class="text-muted small">
                        @if($usingOwn)
                            Mesajlar {{ $clinic->whatsapp_number ?: 'sizin qoşduğunuz nömrə' }} üzərindən gedir.
                        @elseif($whatsappReady)
                            Öz nömrənizi qoşana qədər mesajlar platformanın ortaq nömrəsindən göndərilir.
                        @else
                            Nə sizin bağlantınız, nə də sistem bağlantısı qurulub — bütün bildirişlər SMS ilə gedir.
                        @endif
                    </div>
                </div>
                <a href="{{ route('panel.sms-templates.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-chat-dots me-1"></i>Bildiriş kanalı
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('panel.whatsapp.save') }}">
            @csrf
            @method('PUT')

            {{-- Connection --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-plug me-2 text-success"></i>Bağlantı (WhatsApp Cloud API)</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">
                        Öz WhatsApp Business nömrənizdən mesaj göndərmək üçün Meta hesabınızın məlumatlarını daxil edin.
                        Bu dəyərləri <code>developers.facebook.com</code> » Tətbiqiniz » WhatsApp » API Setup bölməsindən götürürsünüz.
                        Boş buraxsanız mesajlar platformanın ortaq nömrəsindən göndəriləcək.
                    </p>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="whatsapp_enabled" name="whatsapp_enabled" value="1"
                               style="width:2.5em;height:1.3em;cursor:pointer;"
                               {{ $enabled ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium ms-1" for="whatsapp_enabled" style="cursor:pointer;">
                            Öz WhatsApp bağlantımı işlət
                        </label>
                        <div class="text-muted small">
                            Söndürsəniz, məlumatlar saxlanılır, amma mesajlar yenidən sistem nömrəsindən gedir.
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="whatsapp_number" class="form-label fw-medium">WhatsApp nömrəniz</label>
                            <input type="text"
                                   id="whatsapp_number"
                                   name="whatsapp_number"
                                   class="form-control @error('whatsapp_number') is-invalid @enderror"
                                   value="{{ old('whatsapp_number', $clinic->whatsapp_number) }}"
                                   placeholder="994551234567">
                            <div class="form-text">Meta-da təsdiqlənmiş biznes nömrəniz — panel üçün məlumat xarakteri daşıyır.</div>
                            @error('whatsapp_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label for="whatsapp_phone_number_id" class="form-label fw-medium">Phone Number ID</label>
                            <input type="text"
                                   id="whatsapp_phone_number_id"
                                   name="whatsapp_phone_number_id"
                                   class="form-control font-monospace @error('whatsapp_phone_number_id') is-invalid @enderror"
                                   value="{{ old('whatsapp_phone_number_id', $clinic->whatsapp_phone_number_id) }}"
                                   placeholder="123456789012345">
                            <div class="form-text">Mesajın hansı nömrədən getdiyini bu ID müəyyən edir.</div>
                            @error('whatsapp_phone_number_id')
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
                                Token şifrələnmiş saxlanılır və bir daha göstərilmir.
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
                                   value="{{ old('whatsapp_language_code', $clinic->whatsapp_language_code) }}"
                                   placeholder="az">
                            <div class="form-text">Boş qalsa sistem defoltu ({{ $config['language_code'] }}) işlədilir.</div>
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
                        <strong>Meta tərəfindən təsdiqlənmiş şablonla</strong> göndərilə bilər. Şablon adını boş buraxsanız
                        mesaj adi mətn kimi gedir — bu isə yalnız müştəri son 24 saat ərzində sizə yazıbsa çatdırılır.
                        Şablonlar sizin Meta hesabınıza aiddir, ona görə adları burada özünüz yazırsınız.
                    </div>

                    <div class="row g-3">
                        @foreach($templateTypes as $type => $label)
                            <div class="col-md-6">
                                <label for="whatsapp_{{ $type }}_template" class="form-label fw-medium">{{ $label }} — şablon adı</label>
                                <input type="text"
                                       id="whatsapp_{{ $type }}_template"
                                       name="whatsapp_{{ $type }}_template"
                                       class="form-control font-monospace @error('whatsapp_'.$type.'_template') is-invalid @enderror"
                                       value="{{ old('whatsapp_'.$type.'_template', $clinic->{'whatsapp_'.$type.'_template'}) }}"
                                       placeholder="{{ $type }}_sablonu">
                                @error('whatsapp_'.$type.'_template')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="whatsapp_{{ $type }}_params" class="form-label fw-medium">Parametr sırası</label>
                                <input type="text"
                                       id="whatsapp_{{ $type }}_params"
                                       name="whatsapp_{{ $type }}_params"
                                       class="form-control font-monospace @error('whatsapp_'.$type.'_params') is-invalid @enderror"
                                       value="{{ old('whatsapp_'.$type.'_params', $clinic->{'whatsapp_'.$type.'_params'}) }}"
                                       placeholder="{ad_soyad},{tarix},{saat}">
                                <div class="form-text">Şablondakı <code>&#123;&#123;1&#125;&#125;</code>, <code>&#123;&#123;2&#125;&#125;</code>… sırası ilə eyni olmalıdır.</div>
                                @error('whatsapp_'.$type.'_params')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
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
                            Təbriklər üçün əlavə: <code>{ad}</code>, <code>{yas}</code>, <code>{bayram}</code>.
                            Mesajın mətni <a href="{{ route('panel.sms-templates.index') }}">Bildiriş Ayarları</a> səhifəsindəki şablonla eynidir.
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Yadda Saxla
                </button>
                <a href="{{ route('panel.sms-templates.index') }}" class="btn btn-outline-secondary">Geri</a>
            </div>
        </form>

        {{-- Test --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-send-check me-2 text-secondary"></i>Test mesajı</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Bağlantının işlədiyini yoxlamaq üçün bir nömrəyə test mesajı göndərin.
                    Adi mətn yalnız son 24 saat ərzində sizə yazmış nömrəyə çatır — buna görə öz nömrənizi sınayın
                    və əvvəlcə biznes hesabınıza mesaj yazın.
                </p>
                <form method="POST" action="{{ route('panel.whatsapp.test') }}" class="row g-2 align-items-start">
                    @csrf
                    <div class="col-sm-6">
                        <input type="text"
                               name="test_phone"
                               class="form-control @error('test_phone') is-invalid @enderror"
                               value="{{ old('test_phone', $clinic->whatsapp_number ?: $clinic->phone) }}"
                               placeholder="0551234567">
                        @error('test_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-auto">
                        <button type="submit" class="btn btn-outline-success" {{ $whatsappReady ? '' : 'disabled' }}>
                            <i class="bi bi-whatsapp me-1"></i>Göndər
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Disconnect --}}
        @if($clinic->whatsapp_phone_number_id || $hasToken)
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="fw-semibold">Bağlantını sil</div>
                        <div class="text-muted small">
                            Phone Number ID və token silinir; mesajlar yenidən sistem nömrəsindən göndərilir.
                        </div>
                    </div>
                    <form method="POST" action="{{ route('panel.whatsapp.disconnect') }}"
                          data-confirm="WhatsApp bağlantısı silinsin?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>Sil
                        </button>
                    </form>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
