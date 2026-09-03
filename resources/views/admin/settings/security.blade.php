@extends('layouts.admin')

@section('title', 'Təhlükəsizlik')
@section('page-title', 'Təhlükəsizlik — Bot Qoruması')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @error('turnstile_enabled')
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ $message }}
            </div>
        @enderror

        {{-- Status summary --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                     style="width:48px;height:48px;background:{{ $settings['is_live'] ? '#f6821f' : '#e9ecef' }};">
                    <i class="bi bi-shield-check fs-4 {{ $settings['is_live'] ? 'text-white' : 'text-secondary' }}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">
                        Cloudflare Turnstile
                        <span class="badge bg-{{ $settings['is_live'] ? 'success' : 'secondary' }} ms-1">
                            {{ $settings['is_live'] ? 'Aktiv' : 'Deaktiv' }}
                        </span>
                    </div>
                    <div class="text-muted small">
                        @php $liveForms = collect($forms)->filter(fn ($f) => $f['on'])->pluck('label'); @endphp
                        @if($settings['is_live'] && $liveForms->isNotEmpty())
                            Qoruma altında: {{ $liveForms->implode(', ') }}.
                        @elseif($settings['is_live'])
                            Açarlar hazırdır, amma heç bir forma seçilməyib.
                        @else
                            Açarları doldurub aktivləşdirdikdən sonra seçilmiş formalarda bot yoxlaması göstəriləcək.
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Sign-up volume: the number that shows whether the spam stopped --}}
        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-4 fw-bold">{{ $signups['today'] }}</div>
                    <div class="text-muted small">Bu gün qeydiyyat</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-4 fw-bold">{{ $signups['week'] }}</div>
                    <div class="text-muted small">Son 7 gün</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-4 fw-bold">{{ $signups['month'] }}</div>
                    <div class="text-muted small">Son 30 gün</div>
                </div>
            </div>
        </div>

        {{-- Setup guide --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2"></i>Açarları haradan almaq olar</h6>
            </div>
            <div class="card-body small text-muted">
                <ol class="mb-0 ps-3">
                    <li class="mb-2">
                        <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" rel="noopener">dash.cloudflare.com</a>
                        → <strong>Turnstile</strong> → <strong>Add widget</strong>.
                    </li>
                    <li class="mb-2">Domen olaraq <code>{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'innapp.az' }}</code> əlavə edin.</li>
                    <li class="mb-2">Widget Mode: <strong>Managed</strong> (tövsiyə olunur — çox vaxt istifadəçi heç nə etmir).</li>
                    <li class="mb-2">Yaranan <strong>Site Key</strong> və <strong>Secret Key</strong>-i aşağıya yapışdırın.</li>
                    <li>Yadda saxlayın, sonra qeydiyyat səhifəsini gizli rejimdə açıb yoxlayın.</li>
                </ol>
                <div class="alert alert-warning mt-3 mb-0 py-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Yoxlama <strong>fail-closed</strong>-dur: açarlar səhv olsa və ya Cloudflare cavab verməsə, qeydiyyat qəbul edilmir.
                    Ona görə əvvəlcə test edin, sonra aktiv saxlayın.
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Turnstile Ayarları</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.security.save') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="turnstile_enabled" name="turnstile_enabled" value="1"
                               {{ old('turnstile_enabled', $settings['turnstile_enabled']) === '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="turnstile_enabled">
                            Qeydiyyat formalarında bot yoxlamasını aktiv et
                        </label>
                    </div>

                    <div class="border rounded p-3 mb-4 bg-light">
                        <div class="fw-medium mb-1">Hansı formalarda işləsin?</div>
                        <div class="text-muted small mb-3">
                            Yuxarıdakı əsas keçid söndürülübsə, bunların heç biri işləmir.
                        </div>
                        @foreach($forms as $key => $form)
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="form_{{ $key }}" name="form_{{ $key }}" value="1"
                                       {{ old('form_' . $key, $form['on'] ? '1' : '0') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="form_{{ $key }}">
                                    {{ $form['label'] }}
                                    <span class="text-muted small d-block">{{ $form['hint'] }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label for="turnstile_site_key" class="form-label fw-medium">Site Key</label>
                        <input type="text" class="form-control @error('turnstile_site_key') is-invalid @enderror"
                               id="turnstile_site_key" name="turnstile_site_key"
                               value="{{ old('turnstile_site_key', $settings['turnstile_site_key']) }}"
                               placeholder="0x4AAAAAAA...">
                        <div class="form-text small">Səhifədə açıq görünür — gizli deyil.</div>
                        @error('turnstile_site_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="turnstile_secret_key" class="form-label fw-medium">
                            Secret Key
                            @if($settings['has_secret'])
                                <span class="badge bg-success ms-1">Saxlanılıb</span>
                            @else
                                <span class="badge bg-secondary ms-1">Boşdur</span>
                            @endif
                        </label>
                        <input type="password" class="form-control @error('turnstile_secret_key') is-invalid @enderror"
                               id="turnstile_secret_key" name="turnstile_secret_key" autocomplete="new-password"
                               placeholder="{{ $settings['has_secret'] ? 'Dəyişmək üçün yeni açar yazın' : '0x4AAAAAAA...' }}">
                        <div class="form-text small">
                            Şifrələnmiş şəkildə saxlanılır. Boş buraxsanız mövcud açar dəyişmir.
                        </div>
                        @error('turnstile_secret_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Yadda saxla
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-speedometer2 me-2"></i>Əlavə qorunma</h6>
            </div>
            <div class="card-body small text-muted mb-0">
                Qeydiyyat, demo və şifrə bərpası endpoint-ləri həmçinin <strong>dəqiqədə 10 sorğu</strong> limiti ilə
                məhdudlaşdırılıb (IP üzrə). Bu limit koddadır və Turnstile-dan asılı deyil — yoxlama söndürülsə belə işləyir.
            </div>
        </div>

    </div>
</div>
@endsection
