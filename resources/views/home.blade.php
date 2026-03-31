@extends('layouts.public')

@section('title', 'InnApp — Klinika İdarəetmə Sistemi')

@push('styles')
<style>
    /* ── Arctic Frost palette (inherited from layout) ── */
    :root {
        --af-steel:    #4a6fa5;
        --af-steel-dk: #3a5a8c;
        --af-ice:      #d4e4f7;
        --af-ice-lt:   #edf4fd;
        --af-silver:   #c0c0c0;
        --af-white:    #fafafa;
        --af-dark:     #1e2d3d;
        --af-mid:      #3d5166;
        --af-muted:    #64748b;
    }

    /* ── Hero ── */
    .hero-section {
        background: linear-gradient(135deg, #1e2d3d 0%, #2d4a6e 55%, #3a5a8c 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
        padding: 80px 0;
    }
    .hero-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4e4f7' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(212, 228, 247, 0.15);
        border: 1px solid rgba(212, 228, 247, 0.3);
        color: var(--af-ice);
        font-size: .8rem;
        font-weight: 600;
        padding: 5px 14px;
        border-radius: 50px;
        margin-bottom: 22px;
        letter-spacing: .03em;
    }
    .hero-title {
        font-size: clamp(2rem, 5vw, 3.4rem);
        font-weight: 800;
        color: #fff;
        line-height: 1.15;
        letter-spacing: -1px;
        margin-bottom: 20px;
    }
    .hero-title .highlight {
        color: var(--af-ice);
        position: relative;
    }
    .hero-subtitle {
        color: rgba(255,255,255,.72);
        font-size: 1.1rem;
        line-height: 1.7;
        max-width: 500px;
        margin-bottom: 36px;
    }
    .btn-hero-primary {
        background: var(--af-ice);
        color: var(--af-dark) !important;
        font-weight: 700;
        font-size: 1rem;
        padding: 13px 30px;
        border-radius: 10px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all .25s;
        border: none;
    }
    .btn-hero-primary:hover {
        background: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(212,228,247,.3);
    }
    .btn-hero-secondary {
        background: rgba(255,255,255,.12);
        color: #fff !important;
        font-weight: 600;
        font-size: 1rem;
        padding: 13px 30px;
        border-radius: 10px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all .25s;
        border: 1.5px solid rgba(255,255,255,.3);
    }
    .btn-hero-secondary:hover {
        background: rgba(255,255,255,.2);
        border-color: rgba(255,255,255,.5);
    }

    /* Hero mockup */
    .hero-mockup {
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(212,228,247,.18);
        border-radius: 16px;
        overflow: hidden;
        backdrop-filter: blur(4px);
        box-shadow: 0 32px 80px rgba(0,0,0,.35);
    }
    .mockup-topbar {
        background: rgba(255,255,255,.08);
        padding: 10px 16px;
        display: flex;
        align-items: center;
        gap: 6px;
        border-bottom: 1px solid rgba(255,255,255,.1);
    }
    .mockup-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
    }
    .mockup-body { padding: 20px; }
    .mockup-stat {
        background: rgba(255,255,255,.08);
        border-radius: 10px;
        padding: 14px;
        text-align: center;
        border: 1px solid rgba(212,228,247,.12);
    }
    .mockup-stat-num { font-size: 1.5rem; font-weight: 700; color: #fff; line-height: 1; }
    .mockup-stat-lbl { font-size: .68rem; color: rgba(255,255,255,.55); margin-top: 4px; }
    .mockup-table-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        border-radius: 7px;
        background: rgba(255,255,255,.05);
        margin-bottom: 6px;
        font-size: .78rem;
        color: rgba(255,255,255,.8);
    }
    .mockup-avatar {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: var(--af-steel);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .65rem;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }
    .mockup-badge {
        margin-left: auto;
        font-size: .65rem;
        padding: 2px 8px;
        border-radius: 50px;
        font-weight: 600;
    }

    /* hero stats strip */
    .hero-stats-strip {
        position: relative;
        z-index: 10;
        background: rgba(255,255,255,.07);
        border-top: 1px solid rgba(212,228,247,.15);
        padding: 20px 0;
    }
    .hero-stat-item { text-align: center; }
    .hero-stat-item .num { font-size: 1.7rem; font-weight: 800; color: var(--af-ice); line-height: 1; }
    .hero-stat-item .lbl { font-size: .78rem; color: rgba(255,255,255,.55); margin-top: 4px; }

    /* ── Section commons ── */
    .section-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--af-steel);
        margin-bottom: 12px;
    }
    .section-label::before {
        content: '';
        display: block;
        width: 22px;
        height: 2px;
        background: var(--af-steel);
        border-radius: 2px;
    }
    .section-title {
        font-size: clamp(1.6rem, 3vw, 2.25rem);
        font-weight: 800;
        color: var(--af-dark);
        line-height: 1.2;
        letter-spacing: -.5px;
        margin-bottom: 14px;
    }
    .section-subtitle {
        color: var(--af-muted);
        font-size: 1.05rem;
        line-height: 1.7;
        max-width: 560px;
    }

    /* ── Features ── */
    .features-section {
        background: var(--af-white);
        padding: 90px 0;
    }
    .feature-card {
        background: #fff;
        border: 1px solid #e8f0fb;
        border-radius: 16px;
        padding: 28px 24px;
        height: 100%;
        transition: all .28s ease;
        position: relative;
        overflow: hidden;
    }
    .feature-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--af-steel), var(--af-ice));
        opacity: 0;
        transition: opacity .28s;
    }
    .feature-card:hover {
        transform: translateY(-4px);
        border-color: var(--af-ice);
        box-shadow: 0 16px 48px rgba(74,111,165,.12);
    }
    .feature-card:hover::after { opacity: 1; }
    .feature-icon {
        width: 52px; height: 52px;
        border-radius: 14px;
        background: var(--af-ice-lt);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        font-size: 1.4rem;
        color: var(--af-steel);
        flex-shrink: 0;
    }
    .feature-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--af-dark);
        margin-bottom: 8px;
    }
    .feature-desc {
        font-size: .88rem;
        color: var(--af-muted);
        line-height: 1.65;
        margin: 0;
    }

    /* ── How it works ── */
    .how-section {
        background: var(--af-ice-lt);
        padding: 90px 0;
    }
    .step-card {
        background: #fff;
        border-radius: 16px;
        padding: 32px 24px;
        height: 100%;
        border: 1px solid #deeaf8;
        position: relative;
        text-align: center;
    }
    .step-number {
        width: 52px; height: 52px;
        border-radius: 50%;
        background: var(--af-steel);
        color: #fff;
        font-size: 1.2rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        box-shadow: 0 6px 20px rgba(74,111,165,.35);
    }
    .step-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--af-dark);
        margin-bottom: 10px;
    }
    .step-desc {
        font-size: .88rem;
        color: var(--af-muted);
        line-height: 1.65;
        margin: 0;
    }
    .step-connector {
        position: absolute;
        top: 50px;
        right: -18px;
        font-size: 1.3rem;
        color: var(--af-silver);
        z-index: 2;
    }

    /* ── Packages ── */
    .packages-section {
        background: var(--af-white);
        padding: 90px 0;
    }
    .package-card {
        background: #fff;
        border: 1.5px solid #deeaf8;
        border-radius: 20px;
        padding: 34px 28px;
        height: 100%;
        transition: all .28s ease;
        position: relative;
    }
    .package-card.featured {
        border-color: var(--af-steel);
        background: linear-gradient(160deg, #edf4fd 0%, #fff 100%);
        box-shadow: 0 20px 60px rgba(74,111,165,.18);
    }
    .package-card:hover:not(.featured) {
        border-color: var(--af-steel);
        box-shadow: 0 12px 36px rgba(74,111,165,.12);
        transform: translateY(-3px);
    }
    .package-badge-popular {
        position: absolute;
        top: -13px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--af-steel);
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: 4px 16px;
        border-radius: 50px;
        white-space: nowrap;
    }
    .package-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--af-dark);
        margin-bottom: 6px;
    }
    .package-price-block { margin: 18px 0 22px; }
    .package-price {
        font-size: 2.4rem;
        font-weight: 800;
        color: var(--af-steel);
        line-height: 1;
    }
    .package-price sup { font-size: 1rem; font-weight: 700; vertical-align: top; margin-top: 8px; }
    .package-price span { font-size: .9rem; font-weight: 500; color: var(--af-muted); }
    .package-desc {
        font-size: .85rem;
        color: var(--af-muted);
        margin-bottom: 22px;
        line-height: 1.6;
    }
    .package-feature {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: .875rem;
        color: var(--af-mid);
        margin-bottom: 10px;
    }
    .package-feature i { color: var(--af-steel); flex-shrink: 0; margin-top: 2px; }
    .btn-package {
        display: block;
        text-align: center;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        font-size: .95rem;
        text-decoration: none;
        transition: all .22s;
        margin-top: 26px;
    }
    .btn-package-primary {
        background: var(--af-steel);
        color: #fff !important;
    }
    .btn-package-primary:hover {
        background: var(--af-steel-dk);
        box-shadow: 0 6px 20px rgba(74,111,165,.35);
        transform: translateY(-1px);
    }
    .btn-package-outline {
        border: 1.5px solid var(--af-steel);
        color: var(--af-steel) !important;
        background: transparent;
    }
    .btn-package-outline:hover {
        background: var(--af-ice-lt);
    }

    /* ── CTA Banner ── */
    .cta-section {
        background: linear-gradient(135deg, var(--af-steel) 0%, #2d4a6e 100%);
        padding: 80px 0;
        position: relative;
        overflow: hidden;
    }
    .cta-section::before {
        content: '';
        position: absolute;
        width: 500px; height: 500px;
        border-radius: 50%;
        background: rgba(212,228,247,.07);
        top: -200px; right: -100px;
        pointer-events: none;
    }
    .cta-section::after {
        content: '';
        position: absolute;
        width: 300px; height: 300px;
        border-radius: 50%;
        background: rgba(212,228,247,.06);
        bottom: -150px; left: -50px;
        pointer-events: none;
    }

    /* ── Testimonials ── */
    .testimonials-section {
        background: var(--af-ice-lt);
        padding: 90px 0;
    }
    .testimonial-card {
        background: #fff;
        border-radius: 16px;
        padding: 28px 24px;
        border: 1px solid #deeaf8;
        height: 100%;
    }
    .testimonial-stars { color: #f59e0b; font-size: .85rem; margin-bottom: 14px; }
    .testimonial-text {
        font-size: .9rem;
        color: var(--af-mid);
        line-height: 1.7;
        margin-bottom: 18px;
        font-style: italic;
    }
    .testimonial-author { display: flex; align-items: center; gap: 12px; }
    .testimonial-avatar {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: var(--af-ice);
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .85rem; color: var(--af-steel);
        flex-shrink: 0;
    }
    .testimonial-name { font-size: .88rem; font-weight: 700; color: var(--af-dark); }
    .testimonial-role { font-size: .77rem; color: var(--af-muted); }
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════
     HERO
══════════════════════════════════════════════ --}}
<section class="hero-section">
    <div class="container position-relative" style="z-index:2">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-badge">
                    <i class="bi bi-stars"></i>
                    Azərbaycanda №1 Klinika CRM-i
                </div>
                <h1 class="hero-title">
                    Klinikanızı<br>
                    <span class="highlight">ağıllı idarə</span><br>
                    edin
                </h1>
                <p class="hero-subtitle">
                    Randevular, xəstə qeydiyyatı, SMS bildirişlər, hesabatlar — hamısı bir platformada. Vaxtınızı idarəçilikdən yox, xəstələrinizdən ötrü ayırın.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-hero-primary">
                        <i class="bi bi-rocket-takeoff-fill"></i>
                        Pulsuz başla
                    </a>
                    <a href="{{ route('demo.start') }}" class="btn-hero-secondary">
                        <i class="bi bi-play-circle"></i>
                        Canlı demo
                    </a>
                </div>
                <div class="mt-4 d-flex align-items-center gap-3">
                    <div class="d-flex">
                        @foreach(['NM','AH','SQ','RJ'] as $i => $initials)
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                             style="width:32px;height:32px;background:{{ ['#4a6fa5','#3a5a8c','#6a8fc5','#2d4a6e'][$i] }};color:#fff;font-size:.7rem;border:2px solid rgba(255,255,255,.3);margin-left:{{ $i>0 ? '-8px' : '0' }};z-index:{{ 10-$i }}">
                            {{ $initials }}
                        </div>
                        @endforeach
                    </div>
                    <span style="font-size:.82rem;color:rgba(255,255,255,.65)">
                        <strong style="color:#fff">200+</strong> klinika artıq istifadə edir
                    </span>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-mockup">
                    <div class="mockup-topbar">
                        <div class="mockup-dot" style="background:#ff5f57"></div>
                        <div class="mockup-dot" style="background:#febc2e"></div>
                        <div class="mockup-dot" style="background:#28c840"></div>
                        <span style="font-size:.72rem;color:rgba(255,255,255,.4);margin-left:8px">InnApp — Dashboard</span>
                    </div>
                    <div class="mockup-body">
                        <div class="row g-2 mb-3">
                            @foreach([['142','Xəstə'],['8','Bu gün'],['23','Bu ay'],['97%','Doldurulma']] as $s)
                            <div class="col-3">
                                <div class="mockup-stat">
                                    <div class="mockup-stat-num">{{ $s[0] }}</div>
                                    <div class="mockup-stat-lbl">{{ $s[1] }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div style="font-size:.72rem;color:rgba(255,255,255,.45);margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:.05em">Bu Günkü Randevular</div>
                        @foreach([
                            ['NA','Nigar Abbasova','09:00','Müalicə','confirmed'],
                            ['TH','Tural Həsənov','10:30','Kanalın dolması','pending'],
                            ['SM','Sevinc Məmmədli','12:00','Diş çəkilməsi','completed'],
                            ['EQ','Elnur Quliyev','14:30','Konsultasiya','confirmed'],
                        ] as $r)
                        <div class="mockup-table-row">
                            <div class="mockup-avatar">{{ $r[0] }}</div>
                            <div>
                                <div style="font-weight:600;font-size:.77rem">{{ $r[1] }}</div>
                                <div style="font-size:.65rem;color:rgba(255,255,255,.4)">{{ $r[2] }} · {{ $r[3] }}</div>
                            </div>
                            @php
                                $bc = ['confirmed'=>'rgba(74,111,165,.6)','pending'=>'rgba(255,193,7,.5)','completed'=>'rgba(40,200,64,.5)'];
                                $lc = ['confirmed'=>'#d4e4f7','pending'=>'#fff3cd','completed'=>'#d4edda'];
                                $lb = ['confirmed'=>'Təsdiqləndi','pending'=>'Gözləyir','completed'=>'Tamamlandı'];
                            @endphp
                            <span class="mockup-badge" style="background:{{ $bc[$r[4]] }};color:{{ $lc[$r[4]] }}">{{ $lb[$r[4]] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats strip --}}
    <div class="hero-stats-strip w-100 position-absolute bottom-0">
        <div class="container">
            <div class="row g-3 justify-content-center">
                @foreach([['200+','Aktiv klinika'],['50.000+','İdarə olunan randevu'],['99.9%','Sistem stabillıği'],['5 dəq','Qeydiyyat vaxtı']] as $s)
                <div class="col-6 col-md-3">
                    <div class="hero-stat-item">
                        <div class="num">{{ $s[0] }}</div>
                        <div class="lbl">{{ $s[1] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Demo expired notice --}}
@if(session('demo_expired'))
<div style="background:#fff3cd;border-bottom:2px solid #f59e0b;padding:14px 0;">
    <div class="container d-flex align-items-center gap-3">
        <i class="bi bi-hourglass-bottom text-warning fs-5"></i>
        <span style="font-size:.9rem;color:#92400e">{{ session('demo_expired') }}</span>
        <a href="{{ route('register') }}" class="btn btn-sm btn-warning ms-auto fw-bold">Qeydiyyatdan keç</a>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════
     FEATURES
══════════════════════════════════════════════ --}}
<section class="features-section" id="features">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label mx-auto" style="justify-content:center">Xüsusiyyətlər</div>
            <h2 class="section-title">Klinikanız üçün lazım olan hər şey</h2>
            <p class="section-subtitle mx-auto text-center">Sağlamlıq sektoru üçün xüsusi hazırlanmış alətlər toplusu. Sadə interfeys, güclü funksionallıq.</p>
        </div>
        <div class="row g-4">
            @foreach([
                ['bi-calendar-check','Ağıllı Randevu Sistemi','Real vaxtda randevu yaradın, konfliktləri avtomatik aşkar edin. İş saatlarınıza uyğun çevik cədvəl.'],
                ['bi-people','Xəstə Bazası','Tam xəstə profili: tarixçə, xidmətlər, qeydlər, kontaktlar. Sürətli axtarış imkanı.'],
                ['bi-chat-dots','SMS Bildirişlər','Şablonlar üzrə avtomatik SMS: randevu xatırlatması, təsdiq, ləğv. Azərbaycan operatorları ilə inteqrasiya.'],
                ['bi-bar-chart-line','Gəlir Hesabatları','Aylıq, həftəlik, günlük gəlir analizi. Ən çox tələb olunan xidmətlər, xəstə statistikaları.'],
                ['bi-calendar3','Vizual Təqvim','Rəngli, sürükləyib-burax interaktiv təqvim. Həftəlik və aylıq görünüş, real vaxtda yeniləmə.'],
                ['bi-shield-check','Abunəlik İdarəsi','Çevik paket sistemi. Xəstə limiti, SMS limiti, istifadə statistikaları.'],
            ] as $f)
            <div class="col-sm-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi {{ $f[0] }}"></i>
                    </div>
                    <div class="feature-title">{{ $f[1] }}</div>
                    <p class="feature-desc">{{ $f[2] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     HOW IT WORKS
══════════════════════════════════════════════ --}}
<section class="how-section" id="how-it-works">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label mx-auto" style="justify-content:center">Necə işləyir</div>
            <h2 class="section-title">3 addımda başlayın</h2>
            <p class="section-subtitle mx-auto text-center">Heç bir texniki bilik tələb etmir. 5 dəqiqədə sistemdə işə başlayın.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4 position-relative">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-title">Hesab açın</div>
                    <p class="step-desc">Adınızı, emailinizi, klinika adınızı daxil edin. Qeydiyyat 2 dəqiqə çəkir, kredit kartı tələb olunmur.</p>
                </div>
                <div class="step-connector d-none d-md-block">
                    <i class="bi bi-arrow-right-short"></i>
                </div>
            </div>
            <div class="col-md-4 position-relative">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-title">Sistemi qurun</div>
                    <p class="step-desc">Xidmət növlərini, iş saatlarınızı, SMS şablonlarını tənzimləyin. İntuitive interfeys sizi istiqamətləndirir.</p>
                </div>
                <div class="step-connector d-none d-md-block">
                    <i class="bi bi-arrow-right-short"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-title">İşə başlayın</div>
                    <p class="step-desc">Xəstə əlavə edin, randevu yaradın, SMS göndərin. Klinikanız tam idarə altındadır.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     PACKAGES
══════════════════════════════════════════════ --}}
<section class="packages-section" id="packages">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label mx-auto" style="justify-content:center">Paketlər</div>
            <h2 class="section-title">Klinikanıza uyğun plan seçin</h2>
            <p class="section-subtitle mx-auto text-center">Hər ölçülü klinika üçün uyğun qiymətlər. İstənilən vaxt planı dəyişmək mümkündür.</p>
        </div>

        @if($packages->count())
        <div class="row g-4 justify-content-center">
            @foreach($packages as $i => $pkg)
            @php $featured = $packages->count() > 1 && $i === intdiv($packages->count()-1, 1); @endphp
            <div class="col-sm-6 col-lg-4">
                <div class="package-card {{ $i === 1 ? 'featured' : '' }}">
                    @if($i === 1)
                    <div class="package-badge-popular">Ən məşhur</div>
                    @endif
                    <div class="package-name">{{ $pkg->name }}</div>
                    @if($pkg->description)
                    <p class="package-desc">{{ $pkg->description }}</p>
                    @endif
                    <div class="package-price-block">
                        <div class="package-price">
                            <sup>₼</sup>{{ number_format($pkg->price, 0) }}<span>/ay</span>
                        </div>
                    </div>
                    <div>
                        @if($pkg->patient_limit)
                        <div class="package-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ $pkg->patient_limit }} xəstəyə qədər</span>
                        </div>
                        @else
                        <div class="package-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Limitsiz xəstə</span>
                        </div>
                        @endif
                        @if($pkg->sms_limit)
                        <div class="package-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ $pkg->sms_limit }} SMS/ay</span>
                        </div>
                        @else
                        <div class="package-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Limitsiz SMS</span>
                        </div>
                        @endif
                        <div class="package-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Randevu idarəetməsi</span>
                        </div>
                        <div class="package-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Vizual təqvim</span>
                        </div>
                        <div class="package-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Gəlir hesabatları</span>
                        </div>
                        @if($i >= 1)
                        <div class="package-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Prioritet dəstək</span>
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('register') }}" class="btn-package {{ $i === 1 ? 'btn-package-primary' : 'btn-package-outline' }}">
                        Başla
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- Fallback if no packages in DB --}}
        <div class="row g-4 justify-content-center">
            @foreach([
                ['Başlanğıc','29','50','500',''],
                ['Peşəkar','79','Limitsiz','2000','featured'],
                ['Korporativ','149','Limitsiz','Limitsiz',''],
            ] as $p)
            <div class="col-sm-6 col-lg-4">
                <div class="package-card {{ $p[4] }}">
                    @if($p[4])
                    <div class="package-badge-popular">Ən məşhur</div>
                    @endif
                    <div class="package-name">{{ $p[0] }}</div>
                    <div class="package-price-block">
                        <div class="package-price"><sup>₼</sup>{{ $p[1] }}<span>/ay</span></div>
                    </div>
                    <div>
                        <div class="package-feature"><i class="bi bi-check-circle-fill"></i><span>{{ $p[2] }} xəstə</span></div>
                        <div class="package-feature"><i class="bi bi-check-circle-fill"></i><span>{{ $p[3] }} SMS/ay</span></div>
                        <div class="package-feature"><i class="bi bi-check-circle-fill"></i><span>Randevu idarəetməsi</span></div>
                        <div class="package-feature"><i class="bi bi-check-circle-fill"></i><span>Vizual təqvim</span></div>
                        <div class="package-feature"><i class="bi bi-check-circle-fill"></i><span>Gəlir hesabatları</span></div>
                        @if($p[4])<div class="package-feature"><i class="bi bi-check-circle-fill"></i><span>Prioritet dəstək</span></div>@endif
                    </div>
                    <a href="{{ route('register') }}" class="btn-package {{ $p[4] ? 'btn-package-primary' : 'btn-package-outline' }}">Başla</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <p class="text-center mt-4" style="font-size:.85rem;color:var(--af-muted)">
            <i class="bi bi-shield-check me-1"></i>
            Kredit kartı tələb olunmur · İstənilən vaxt ləğv edin · 14 gün pulsuz sınaq
        </p>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     TESTIMONIALS
══════════════════════════════════════════════ --}}
<section class="testimonials-section">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label mx-auto" style="justify-content:center">Rəylər</div>
            <h2 class="section-title">Həkimlər nə deyir?</h2>
        </div>
        <div class="row g-4">
            @foreach([
                ['NM','Nigar M.','Diş Həkimi, Bakı','★★★★★','InnApp bizim klinikamızın iş prosesini tamamilə dəyişdi. Randevular indi heç vaxt üst-üstə düşmür, xəstələrə vaxtında SMS gedir.'],
                ['TH','Tural H.','Klinika direktoru','★★★★★','Ən çox xoşuma gələn cəhəti — sadəliyi. Köhnə işçilərimiz də 1 gündə sistemə alışdı. Hesabatlar çox faydalıdır.'],
                ['SQ','Sevinc Q.','Ortodontist','★★★★★','SMS xatırlatma xüsusiyyəti xəstə davamiyyətini 40% artırdı. İndi xəstələr randevularını unutmurlar.'],
            ] as $t)
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-stars">{{ $t[3] }}</div>
                    <p class="testimonial-text">"{{ $t[4] }}"</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">{{ $t[0] }}</div>
                        <div>
                            <div class="testimonial-name">{{ $t[1] }}</div>
                            <div class="testimonial-role">{{ $t[2] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CTA BANNER
══════════════════════════════════════════════ --}}
<section class="cta-section">
    <div class="container text-center position-relative" style="z-index:2">
        <div class="d-inline-flex align-items-center gap-2 mb-3" style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);color:var(--af-ice);font-size:.8rem;font-weight:600;padding:5px 16px;border-radius:50px">
            <i class="bi bi-lightning-charge-fill"></i>
            Pulsuz başlayın
        </div>
        <h2 style="font-size:clamp(1.8rem,4vw,2.8rem);font-weight:800;color:#fff;line-height:1.2;letter-spacing:-.5px;margin-bottom:16px">
            Klinikanızı bu gün<br>
            rəqəmsallaşdırın
        </h2>
        <p style="color:rgba(255,255,255,.7);font-size:1.05rem;max-width:500px;margin:0 auto 36px;line-height:1.7">
            14 günlük pulsuz sınaq dövrü ilə riski sıfıra endirin. Kredit kartı tələb olunmur.
        </p>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <a href="{{ route('register') }}" class="btn-hero-primary">
                <i class="bi bi-rocket-takeoff-fill"></i>
                İndi qeydiyyatdan keç
            </a>
            <a href="{{ route('demo.start') }}" class="btn-hero-secondary">
                <i class="bi bi-play-circle"></i>
                Canlı demo
            </a>
        </div>
        <p class="mt-4" style="font-size:.82rem;color:rgba(255,255,255,.45)">
            Artıq hesabınız var? <a href="{{ route('login') }}" style="color:var(--af-ice);text-decoration:none;font-weight:600">Daxil olun →</a>
        </p>
    </div>
</section>

@endsection
