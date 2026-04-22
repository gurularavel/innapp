@extends('layouts.public')

@section('title', 'InnApp — Klinika İdarəetmə Sistemi')

@push('styles')
<style>
    :root {
        --primary:    #0e86d4;
        --primary-dk: #0a6daf;
        --primary-lt: #e8f4fd;
        --teal:       #1bc8c8;
        --teal-lt:    #e4f9f9;
        --dark:       #0e1e35;
        --mid:        #2c4160;
        --muted:      #6b7fa3;
    }

    /* ─── Section helpers ─── */
    .section-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--primary);
        margin-bottom: 14px;
    }
    .section-tag::before {
        content: '';
        width: 28px; height: 2px;
        background: linear-gradient(90deg,var(--primary),var(--teal));
        border-radius: 2px;
        display: block;
    }
    .section-heading {
        font-size: clamp(1.65rem, 3vw, 2.3rem);
        font-weight: 800;
        color: var(--dark);
        line-height: 1.2;
        letter-spacing: -.5px;
        margin-bottom: 14px;
    }
    .section-lead {
        font-size: 1rem;
        color: var(--muted);
        line-height: 1.75;
        max-width: 560px;
    }

    /* ═══════════════════════════════
       HERO
    ═══════════════════════════════ */
    .hero-wrap {
        background: linear-gradient(160deg, #061526 0%, #0e2a4a 50%, #0d3d6e 100%);
        position: relative;
        overflow: hidden;
        padding: 90px 0 0;
    }
    /* floating cross motifs */
    .hero-wrap .cross-decor {
        position: absolute;
        opacity: .05;
        pointer-events: none;
    }
    .hero-wrap .cross-decor.c1 { top: 8%; left: 5%; font-size: 7rem; }
    .hero-wrap .cross-decor.c2 { top: 30%; right: 3%; font-size: 4rem; }
    .hero-wrap .cross-decor.c3 { bottom: 20%; left: 2%; font-size: 3rem; }

    /* tag pill */
    .hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(27,200,200,.14);
        border: 1px solid rgba(27,200,200,.28);
        color: var(--teal);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .04em;
        padding: 6px 16px;
        border-radius: 50px;
        margin-bottom: 24px;
    }
    .hero-tag .dot {
        width: 7px; height: 7px;
        background: var(--teal);
        border-radius: 50%;
        animation: blink 1.6s ease-in-out infinite;
    }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

    .hero-title {
        font-size: clamp(2rem, 5.5vw, 3.5rem);
        font-weight: 800;
        color: #fff;
        line-height: 1.12;
        letter-spacing: -1px;
        margin-bottom: 22px;
    }
    .hero-title em {
        font-style: normal;
        background: linear-gradient(90deg, #0e86d4, #1bc8c8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .hero-subtitle {
        font-size: 1.05rem;
        color: rgba(255,255,255,.65);
        line-height: 1.75;
        max-width: 480px;
        margin-bottom: 36px;
    }
    .btn-primary-hero {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        background: linear-gradient(135deg, var(--primary), var(--teal));
        color: #fff !important;
        font-weight: 700;
        font-size: .97rem;
        padding: 13px 28px;
        border-radius: 10px;
        text-decoration: none;
        transition: transform .2s, box-shadow .2s;
        box-shadow: 0 6px 22px rgba(14,134,212,.4);
        border: none;
    }
    .btn-primary-hero:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(14,134,212,.5); color: #fff !important; }
    .btn-demo-hero {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        background: rgba(255,255,255,.1);
        color: #fff !important;
        font-weight: 600;
        font-size: .97rem;
        padding: 13px 28px;
        border-radius: 10px;
        text-decoration: none;
        border: 1.5px solid rgba(255,255,255,.2);
        transition: all .2s;
    }
    .btn-demo-hero .live-dot {
        width: 8px; height: 8px;
        background: #1bc8c8;
        border-radius: 50%;
        animation: blink 1.4s infinite;
        flex-shrink: 0;
    }
    .btn-demo-hero:hover { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.4); color: #fff !important; }

    /* social proof row */
    .hero-proof {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-top: 32px;
    }
    .hero-avatars { display: flex; }
    .hero-avatars span {
        width: 32px; height: 32px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,.25);
        background: var(--primary);
        display: flex; align-items: center; justify-content: center;
        font-size: .66rem; font-weight: 700; color: #fff;
        margin-left: -8px;
    }
    .hero-avatars span:first-child { margin-left: 0; }
    .hero-proof-text { font-size: .82rem; color: rgba(255,255,255,.6); }
    .hero-proof-text strong { color: #fff; }

    /* dashboard mockup */
    .hero-visual {
        position: relative;
        padding-bottom: 0;
    }
    .dash-window {
        background: #0e2447;
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 16px 16px 0 0;
        overflow: hidden;
        box-shadow: 0 -20px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(255,255,255,.06);
        margin-top: 20px;
    }
    .dash-titlebar {
        background: #071628;
        padding: 11px 16px;
        display: flex;
        align-items: center;
        gap: 6px;
        border-bottom: 1px solid rgba(255,255,255,.07);
    }
    .dash-dot { width: 10px; height: 10px; border-radius: 50%; }
    .dash-url {
        margin-left: 12px;
        background: rgba(255,255,255,.07);
        border-radius: 6px;
        padding: 3px 12px;
        font-size: .7rem;
        color: rgba(255,255,255,.35);
        display: flex; align-items: center; gap: 5px;
    }
    .dash-body { display: flex; min-height: 320px; }
    .dash-sidebar {
        width: 52px;
        background: #091b33;
        border-right: 1px solid rgba(255,255,255,.06);
        display: flex; flex-direction: column; align-items: center;
        gap: 8px;
        padding: 14px 0;
    }
    .dash-si {
        width: 34px; height: 34px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: .9rem; color: rgba(255,255,255,.35);
        transition: background .2s;
    }
    .dash-si.active { background: rgba(14,134,212,.3); color: #0e86d4; }
    .dash-content { flex: 1; padding: 16px; }
    .dash-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 8px; margin-bottom: 14px; }
    .dash-stat {
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 10px;
        padding: 11px 10px;
        text-align: center;
    }
    .dash-stat-n { font-size: 1.1rem; font-weight: 700; color: #fff; line-height: 1; }
    .dash-stat-l { font-size: .62rem; color: rgba(255,255,255,.4); margin-top: 3px; }
    .dash-table-head {
        display: flex;
        gap: 8px;
        padding: 5px 8px;
        font-size: .62rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(255,255,255,.3);
        font-weight: 600;
        margin-bottom: 4px;
    }
    .dash-row {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 8px;
        border-radius: 8px;
        background: rgba(255,255,255,.04);
        margin-bottom: 5px;
    }
    .dash-avatar {
        width: 26px; height: 26px;
        border-radius: 8px;
        background: rgba(14,134,212,.4);
        display: flex; align-items: center; justify-content: center;
        font-size: .6rem; font-weight: 700; color: #fff;
        flex-shrink: 0;
    }
    .dash-name { font-size: .72rem; color: rgba(255,255,255,.75); font-weight: 500; flex: 1; }
    .dash-time { font-size: .65rem; color: rgba(255,255,255,.35); flex-shrink: 0; }
    .dash-badge-ok { font-size: .6rem; padding: 2px 8px; border-radius: 50px; background: rgba(27,200,200,.2); color: #1bc8c8; font-weight: 600; }
    .dash-badge-pend { font-size: .6rem; padding: 2px 8px; border-radius: 50px; background: rgba(255,193,7,.2); color: #ffc107; font-weight: 600; }
    .dash-badge-done { font-size: .6rem; padding: 2px 8px; border-radius: 50px; background: rgba(40,167,69,.2); color: #28a745; font-weight: 600; }

    /* wave separator */
    .wave-sep {
        line-height: 0;
        background: linear-gradient(160deg, #061526 0%, #0d3d6e 100%);
    }
    .wave-sep svg { display: block; width: 100%; }

    /* ═══════════════════════════════
       STATS BAR
    ═══════════════════════════════ */
    .stats-bar {
        background: #fff;
        padding: 36px 0;
        border-bottom: 1px solid #eef2f8;
    }
    .stat-item { text-align: center; }
    .stat-num {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary), var(--teal));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        margin-bottom: 4px;
    }
    .stat-lbl { font-size: .82rem; color: var(--muted); font-weight: 500; }
    .stat-divider {
        width: 1px;
        background: #e8edf4;
        align-self: stretch;
        margin: 4px 0;
    }

    /* ═══════════════════════════════
       FEATURES
    ═══════════════════════════════ */
    .features-section { padding: 96px 0; background: #fff; }
    .feature-item {
        display: flex;
        gap: 20px;
        padding: 24px;
        border-radius: 14px;
        border: 1px solid #eef2f8;
        transition: all .25s;
        background: #fff;
    }
    .feature-item:hover {
        border-color: #c8e4f8;
        box-shadow: 0 8px 32px rgba(14,134,212,.08);
        transform: translateY(-3px);
    }
    .feature-icon-wrap {
        width: 52px; height: 52px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .feature-title { font-size: 1rem; font-weight: 700; color: var(--dark); margin-bottom: 6px; }
    .feature-desc { font-size: .875rem; color: var(--muted); line-height: 1.65; margin: 0; }

    /* feature image block */
    .feature-visual {
        background: linear-gradient(150deg, #0e2447 0%, #0d3d6e 100%);
        border-radius: 20px;
        padding: 32px 28px;
        position: relative;
        overflow: hidden;
    }
    .feature-visual::before {
        content: '';
        position: absolute;
        width: 300px; height: 300px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(27,200,200,.12) 0%, transparent 70%);
        top: -80px; right: -80px;
    }

    /* ═══════════════════════════════
       HOW IT WORKS
    ═══════════════════════════════ */
    .how-section { background: #f5f9ff; padding: 96px 0; }
    .step-wrap {
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .step-item {
        display: flex;
        gap: 24px;
        padding: 28px 0;
        position: relative;
    }
    .step-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 23px;
        top: 70px;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--primary), transparent);
        opacity: .25;
    }
    .step-num {
        width: 48px; height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--primary), var(--teal));
        color: #fff;
        font-size: 1.1rem;
        font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 6px 20px rgba(14,134,212,.3);
        position: relative;
        z-index: 1;
    }
    .step-title { font-size: 1.05rem; font-weight: 700; color: var(--dark); margin-bottom: 6px; }
    .step-desc { font-size: .875rem; color: var(--muted); line-height: 1.65; margin: 0; }

    /* ═══════════════════════════════
       PACKAGES
    ═══════════════════════════════ */
    .packages-section { background: #fff; padding: 96px 0; }
    .pkg-card {
        background: #fff;
        border: 1.5px solid #e5edf7;
        border-radius: 20px;
        padding: 36px 30px;
        height: 100%;
        transition: all .28s;
        position: relative;
    }
    .pkg-card:hover:not(.pkg-featured) {
        border-color: var(--primary);
        box-shadow: 0 12px 40px rgba(14,134,212,.1);
        transform: translateY(-4px);
    }
    .pkg-card.pkg-featured {
        background: linear-gradient(160deg, #0e2447 0%, #0d3d6e 100%);
        border-color: transparent;
        box-shadow: 0 20px 60px rgba(14,30,53,.4);
        transform: translateY(-6px);
    }
    .pkg-popular {
        position: absolute;
        top: -13px; left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, var(--primary), var(--teal));
        color: #fff;
        font-size: .7rem; font-weight: 700;
        letter-spacing: .06em; text-transform: uppercase;
        padding: 4px 18px;
        border-radius: 50px;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(14,134,212,.35);
    }
    .pkg-name { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
    .pkg-featured .pkg-name { color: rgba(255,255,255,.7); }
    .pkg-card:not(.pkg-featured) .pkg-name { color: var(--muted); }
    .pkg-price {
        font-size: 2.6rem;
        font-weight: 800;
        line-height: 1;
        margin: 18px 0 4px;
    }
    .pkg-featured .pkg-price { color: #fff; }
    .pkg-card:not(.pkg-featured) .pkg-price { color: var(--dark); }
    .pkg-price sup { font-size: 1.1rem; vertical-align: top; margin-top: 10px; font-weight: 700; }
    .pkg-price span { font-size: .85rem; font-weight: 500; opacity: .5; }
    .pkg-desc { font-size: .85rem; line-height: 1.6; margin-bottom: 22px; }
    .pkg-featured .pkg-desc { color: rgba(255,255,255,.5); }
    .pkg-card:not(.pkg-featured) .pkg-desc { color: var(--muted); }
    .pkg-feature {
        display: flex; align-items: flex-start; gap: 10px;
        font-size: .875rem; margin-bottom: 10px;
    }
    .pkg-feature i { font-size: .9rem; flex-shrink: 0; margin-top: 1px; }
    .pkg-featured .pkg-feature { color: rgba(255,255,255,.75); }
    .pkg-featured .pkg-feature i { color: var(--teal); }
    .pkg-card:not(.pkg-featured) .pkg-feature { color: var(--mid); }
    .pkg-card:not(.pkg-featured) .pkg-feature i { color: var(--primary); }
    .pkg-divider { border-color: rgba(255,255,255,.1); margin: 20px 0; }
    .pkg-card:not(.pkg-featured) .pkg-divider { border-color: #e8edf4; }
    .btn-pkg-primary {
        display: block; text-align: center;
        padding: 12px 24px; border-radius: 10px;
        font-weight: 700; font-size: .92rem;
        text-decoration: none; transition: all .22s;
        margin-top: 24px;
        background: linear-gradient(135deg, var(--primary), var(--teal));
        color: #fff !important;
        box-shadow: 0 4px 14px rgba(14,134,212,.3);
    }
    .btn-pkg-primary:hover { transform: translateY(-1px); box-shadow: 0 7px 20px rgba(14,134,212,.4); }
    .btn-pkg-outline {
        display: block; text-align: center;
        padding: 12px 24px; border-radius: 10px;
        font-weight: 700; font-size: .92rem;
        text-decoration: none; transition: all .22s;
        margin-top: 24px;
        border: 1.5px solid var(--primary);
        color: var(--primary) !important;
        background: transparent;
    }
    .btn-pkg-outline:hover { background: var(--primary-lt); }

    /* ═══════════════════════════════
       TESTIMONIALS
    ═══════════════════════════════ */
    .testimonials-section { background: #f5f9ff; padding: 96px 0; }
    .testimonial-card {
        background: #fff;
        border-radius: 16px;
        padding: 30px;
        border: 1px solid #e5edf7;
        height: 100%;
        position: relative;
    }
    .testimonial-quote {
        font-size: 3rem;
        line-height: 1;
        color: var(--primary-lt);
        font-family: Georgia, serif;
        position: absolute;
        top: 18px; left: 24px;
    }
    .testimonial-stars { font-size: .8rem; color: #f59e0b; letter-spacing: 1px; margin-bottom: 14px; padding-top: 24px; }
    .testimonial-text { font-size: .9rem; color: var(--mid); line-height: 1.75; margin-bottom: 20px; }
    .testimonial-author { display: flex; align-items: center; gap: 12px; }
    .t-avatar {
        width: 42px; height: 42px; border-radius: 12px;
        background: linear-gradient(135deg, var(--primary), var(--teal));
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .88rem; color: #fff; flex-shrink: 0;
    }
    .t-name { font-size: .9rem; font-weight: 700; color: var(--dark); }
    .t-role { font-size: .77rem; color: var(--muted); }

    /* ═══════════════════════════════
       CTA
    ═══════════════════════════════ */
    .cta-section {
        background: linear-gradient(135deg, #0a2245 0%, #0e3d6e 60%, #0d5a8a 100%);
        padding: 88px 0;
        position: relative;
        overflow: hidden;
    }
    .cta-section .cta-glow {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }
    .cta-section .cta-glow.g1 {
        width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(27,200,200,.1) 0%, transparent 70%);
        top: -200px; right: -100px;
    }
    .cta-section .cta-glow.g2 {
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(14,134,212,.15) 0%, transparent 70%);
        bottom: -150px; left: 0;
    }
    .cta-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(27,200,200,.14);
        border: 1px solid rgba(27,200,200,.25);
        color: var(--teal);
        font-size: .76rem; font-weight: 700; letter-spacing: .05em;
        padding: 5px 16px; border-radius: 50px;
        margin-bottom: 20px;
    }

    /* ═══════════════════════════════
       FLOATING DEMO BUTTON
    ═══════════════════════════════ */
    .demo-float {
        position: fixed;
        bottom: 28px; right: 28px;
        z-index: 999;
        display: flex; flex-direction: column; align-items: flex-end; gap: 6px;
    }
    .demo-float-label {
        background: rgba(14,30,53,.9);
        color: rgba(255,255,255,.65);
        font-size: .7rem; font-weight: 600;
        padding: 3px 10px; border-radius: 50px;
        backdrop-filter: blur(8px);
        letter-spacing: .04em;
    }
    .demo-float-btn {
        display: flex; align-items: center; gap: 9px;
        background: linear-gradient(135deg, var(--primary), var(--teal));
        color: #fff !important;
        font-weight: 700; font-size: .88rem;
        padding: 11px 20px;
        border-radius: 50px;
        text-decoration: none;
        box-shadow: 0 6px 22px rgba(14,134,212,.4);
        animation: float-pulse 2.2s infinite;
        white-space: nowrap;
    }
    .demo-float-btn:hover { animation: none; transform: scale(1.04); box-shadow: 0 10px 30px rgba(14,134,212,.5); color: #fff !important; }
    @keyframes float-pulse {
        0%   { box-shadow: 0 6px 22px rgba(14,134,212,.4); }
        50%  { box-shadow: 0 6px 30px rgba(27,200,200,.45); }
        100% { box-shadow: 0 6px 22px rgba(14,134,212,.4); }
    }
    .demo-float-btn .live-dot { width: 7px; height: 7px; background: #fff; border-radius: 50%; animation: blink 1.4s infinite; flex-shrink: 0; }
    @media (max-width: 575px) { .demo-float { bottom: 16px; right: 16px; } }
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════
     HERO
══════════════════════════════════════════════ --}}
<div class="hero-wrap">
    <i class="bi bi-plus-lg cross-decor c1"></i>
    <i class="bi bi-plus-circle cross-decor c2"></i>
    <i class="bi bi-plus cross-decor c3"></i>

    <div class="container position-relative" style="z-index:2">
        <div class="row align-items-end g-5">
            {{-- Left: text --}}
            <div class="col-lg-5" style="padding-bottom: 70px">
                <h1 class="hero-title">
                    Klinikanızı<br>
                    <em>ağıllı idarə</em><br>
                    edin
                </h1>
                <p class="hero-subtitle">
                    Randevular, xəstə qeydiyyatı, SMS bildirişlər, hesabatlar — hamısı bir platformada. Vaxtınızı idarəçilikdən yox, xəstələrinizdən ötrü ayırın.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-primary-hero">
                        <i class="bi bi-rocket-takeoff-fill"></i>
                        Pulsuz başla
                    </a>
                    <a href="{{ route('demo.start') }}" class="btn-demo-hero">
                        <span class="live-dot"></span>
                        Canlı demo
                    </a>
                </div>
            </div>

            {{-- Right: dashboard mockup --}}
            <div class="col-lg-7 d-none d-lg-block">
                <div class="hero-visual">
                    <div class="dash-window">
                        <div class="dash-titlebar">
                            <div class="dash-dot" style="background:#ff5f57"></div>
                            <div class="dash-dot" style="background:#febc2e"></div>
                            <div class="dash-dot" style="background:#28c840"></div>
                            <div class="dash-url">
                                <i class="bi bi-lock-fill" style="font-size:.6rem"></i>
                                innapp.az/panel/dashboard
                            </div>
                        </div>
                        <div class="dash-body">
                            <div class="dash-sidebar">
                                <div class="dash-si active"><i class="bi bi-speedometer2"></i></div>
                                <div class="dash-si"><i class="bi bi-people"></i></div>
                                <div class="dash-si"><i class="bi bi-calendar-check"></i></div>
                                <div class="dash-si"><i class="bi bi-calendar3"></i></div>
                                <div class="dash-si"><i class="bi bi-bar-chart-line"></i></div>
                            </div>
                            <div class="dash-content">
                                <div class="dash-stats">
                                    @foreach([['142','Xəstə'],['8','Bu gün'],['97%','Doldurulma'],['23','Bu ay']] as $s)
                                    <div class="dash-stat">
                                        <div class="dash-stat-n">{{ $s[0] }}</div>
                                        <div class="dash-stat-l">{{ $s[1] }}</div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="dash-table-head">
                                    <span style="flex:1">Xəstə</span>
                                    <span style="width:50px">Saat</span>
                                    <span style="width:70px">Status</span>
                                </div>
                                @foreach([
                                    ['NA','Nigar Abbasova','09:00','ok'],
                                    ['TH','Tural Həsənov','10:30','pend'],
                                    ['SM','Sevinc Məmmədli','12:00','done'],
                                    ['EQ','Elnur Quliyev','14:30','ok'],
                                ] as $r)
                                <div class="dash-row">
                                    <div class="dash-avatar">{{ $r[0] }}</div>
                                    <div class="dash-name">{{ $r[1] }}</div>
                                    <div class="dash-time">{{ $r[2] }}</div>
                                    @if($r[3]==='ok')<span class="dash-badge-ok">Təsdiqləndi</span>
                                    @elseif($r[3]==='pend')<span class="dash-badge-pend">Gözləyir</span>
                                    @else<span class="dash-badge-done">Tamamlandı</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- wave bottom --}}
    <div class="wave-sep">
        <svg viewBox="0 0 1440 70" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C360,80 1080,0 1440,40 L1440,70 L0,70 Z" fill="#ffffff"/>
        </svg>
    </div>
</div>

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
     STATS BAR
══════════════════════════════════════════════ --}}
<div class="stats-bar">
    <div class="container">
        <div class="row align-items-center justify-content-center g-4">
            @foreach([['200+','Aktiv klinika'],['50K+','İdarə olunan randevu'],['99.9%','Sistem stabillıği'],['5 dəq','Qeydiyyat vaxtı']] as $i => $s)
            @if($i > 0)<div class="stat-divider d-none d-md-block"></div>@endif
            <div class="col-6 col-md-auto">
                <div class="stat-item">
                    <div class="stat-num">{{ $s[0] }}</div>
                    <div class="stat-lbl">{{ $s[1] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     FEATURES
══════════════════════════════════════════════ --}}
<section class="features-section" id="features">
    <div class="container">
        <div class="row align-items-center g-5">
            {{-- Left: text + feature list --}}
            <div class="col-lg-6">
                <div class="section-tag">Xüsusiyyətlər</div>
                <h2 class="section-heading">Klinikanız üçün<br>lazım olan hər şey</h2>
                <p class="section-lead mb-5">Sağlamlıq sektoru üçün xüsusi hazırlanmış alətlər toplusu. Sadə interfeys, güclü funksionallıq.</p>

                <div class="d-flex flex-column gap-3">
                    @foreach([
                        ['bi-calendar-check','#0e86d4','#e8f4fd','Ağıllı Randevu Sistemi','Real vaxtda randevu yaradın, konfliktləri avtomatik aşkar edin.'],
                        ['bi-people','#1bc8c8','#e4f9f9','Xəstə Bazası','Tam xəstə profili: tarixçə, xidmətlər, qeydlər, kontaktlar.'],
                        ['bi-chat-dots','#8b5cf6','#f0ebff','SMS Bildirişlər','Şablonlar üzrə avtomatik SMS. Azərbaycan operatorları ilə inteqrasiya.'],
                        ['bi-bar-chart-line','#f59e0b','#fef6e4','Gəlir Hesabatları','Aylıq, həftəlik, günlük gəlir analizi. Xidmət statistikaları.'],
                    ] as $f)
                    <div class="feature-item">
                        <div class="feature-icon-wrap" style="background:{{ $f[2] }};color:{{ $f[1] }}">
                            <i class="bi {{ $f[0] }}"></i>
                        </div>
                        <div>
                            <div class="feature-title">{{ $f[3] }}</div>
                            <p class="feature-desc">{{ $f[4] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: visual panel --}}
            <div class="col-lg-6 d-none d-lg-block">
                <div class="feature-visual">
                    <div style="font-size:.7rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px">Bu həftəki randevular</div>
                    @foreach([
                        ['Mon','8','#0e86d4'],['Tue','12','#1bc8c8'],['Wed','6','#0e86d4'],
                        ['Thu','15','#1bc8c8'],['Fri','9','#0e86d4'],
                    ] as $b)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span style="width:32px;font-size:.72rem;color:rgba(255,255,255,.4);flex-shrink:0">{{ $b[0] }}</span>
                        <div style="flex:1;height:8px;border-radius:4px;background:rgba(255,255,255,.08);overflow:hidden">
                            <div style="height:100%;width:{{ ['53','80','40','100','60'][$loop->index] }}%;background:{{ $b[2] }};border-radius:4px;opacity:.85"></div>
                        </div>
                        <span style="font-size:.78rem;font-weight:700;color:#fff;width:20px;text-align:right">{{ $b[1] }}</span>
                    </div>
                    @endforeach

                    <hr style="border-color:rgba(255,255,255,.1);margin:20px 0">

                    <div style="font-size:.7rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px">SMS statistikası</div>
                    <div class="row g-3">
                        @foreach([['124','Göndərildi','#1bc8c8'],['98%','Çatdırılma','#0e86d4'],['12','Cavab','#f59e0b']] as $s)
                        <div class="col-4">
                            <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:12px;text-align:center">
                                <div style="font-size:1.3rem;font-weight:800;color:{{ $s[2] }}">{{ $s[0] }}</div>
                                <div style="font-size:.65rem;color:rgba(255,255,255,.4);margin-top:3px">{{ $s[1] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom 2 extra features --}}
        <div class="row g-3 mt-5">
            @foreach([
                ['bi-calendar3','#1bc8c8','#e4f9f9','Vizual Təqvim','Rəngli, interaktiv təqvim. Həftəlik və aylıq görünüş, real vaxtda yeniləmə.'],
                ['bi-shield-check','#0e86d4','#e8f4fd','Abunəlik İdarəsi','Çevik paket sistemi. Xəstə limiti, SMS limiti, istifadə statistikaları.'],
            ] as $f)
            <div class="col-md-6">
                <div class="feature-item">
                    <div class="feature-icon-wrap" style="background:{{ $f[2] }};color:{{ $f[1] }}">
                        <i class="bi {{ $f[0] }}"></i>
                    </div>
                    <div>
                        <div class="feature-title">{{ $f[3] }}</div>
                        <p class="feature-desc">{{ $f[4] }}</p>
                    </div>
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
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <div class="section-tag">Necə işləyir</div>
                <h2 class="section-heading">3 addımda<br>işə başlayın</h2>
                <p class="section-lead">Heç bir texniki bilik tələb etmir. 5 dəqiqədə sistemdə işə başlayın.</p>
                <a href="{{ route('register') }}" class="btn-primary-hero mt-4 d-inline-flex">
                    <i class="bi bi-rocket-takeoff-fill"></i>İndi başla
                </a>
            </div>
            <div class="col-lg-7">
                <div class="step-wrap ps-lg-3">
                    @foreach([
                        ['Hesab açın','Adınızı, emailinizi, klinika adınızı daxil edin. Qeydiyyat 2 dəqiqə çəkir, kredit kartı tələb olunmur.'],
                        ['Sistemi qurun','Xidmət növlərini, iş saatlarınızı, SMS şablonlarını tənzimləyin. İntuitive interfeys sizi istiqamətləndirir.'],
                        ['İşə başlayın','Xəstə əlavə edin, randevu yaradın, SMS göndərin. Klinikanız tam idarə altındadır.'],
                    ] as $i => $s)
                    <div class="step-item">
                        <div class="step-num">{{ $i+1 }}</div>
                        <div class="pt-1">
                            <div class="step-title">{{ $s[0] }}</div>
                            <p class="step-desc">{{ $s[1] }}</p>
                        </div>
                    </div>
                    @endforeach
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
            <div class="section-tag" style="justify-content:center;margin:0 auto 14px">Paketlər</div>
            <h2 class="section-heading">Klinikanıza uyğun plan seçin</h2>
            <p class="section-lead mx-auto text-center">İstənilən vaxt planı dəyişmək mümkündür. Kredit kartı tələb olunmur.</p>
        </div>

        @if($packages->count())
        <div class="row g-4 justify-content-center align-items-start">
            @foreach($packages as $i => $pkg)
            <div class="col-sm-6 col-lg-4">
                <div class="pkg-card {{ $i === 1 ? 'pkg-featured' : '' }}">
                    @if($i === 1)<div class="pkg-popular">Ən məşhur</div>@endif
                    <div class="pkg-name">{{ $pkg->name }}</div>
                    <div class="pkg-price"><sup>₼</sup>{{ number_format($pkg->price,0) }}<span>/ay</span></div>
                    @if($pkg->description)<p class="pkg-desc">{{ $pkg->description }}</p>@endif
                    <hr class="pkg-divider">
                    @if($pkg->patient_limit)
                        <div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>{{ $pkg->patient_limit }} xəstəyə qədər</span></div>
                    @else
                        <div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>Limitsiz xəstə</span></div>
                    @endif
                    @if($pkg->sms_limit)
                        <div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>{{ $pkg->sms_limit }} SMS/ay</span></div>
                    @else
                        <div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>Limitsiz SMS</span></div>
                    @endif
                    <div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>Randevu idarəetməsi</span></div>
                    <div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>Vizual təqvim</span></div>
                    <div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>Gəlir hesabatları</span></div>
                    @if($i >= 1)<div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>Prioritet dəstək</span></div>@endif
                    <a href="{{ route('register') }}" class="{{ $i === 1 ? 'btn-pkg-primary' : 'btn-pkg-outline' }}">Başla</a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="row g-4 justify-content-center align-items-start">
            @foreach([['Başlanğıc','29','50','500',''],['Peşəkar','79','Limitsiz','2000','pkg-featured'],['Korporativ','149','Limitsiz','Limitsiz','']] as $i => $p)
            <div class="col-sm-6 col-lg-4">
                <div class="pkg-card {{ $p[4] }}">
                    @if($p[4])<div class="pkg-popular">Ən məşhur</div>@endif
                    <div class="pkg-name">{{ $p[0] }}</div>
                    <div class="pkg-price"><sup>₼</sup>{{ $p[1] }}<span>/ay</span></div>
                    <hr class="pkg-divider">
                    <div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>{{ $p[2] }} xəstə</span></div>
                    <div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>{{ $p[3] }} SMS/ay</span></div>
                    <div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>Randevu idarəetməsi</span></div>
                    <div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>Vizual təqvim</span></div>
                    <div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>Gəlir hesabatları</span></div>
                    @if($p[4])<div class="pkg-feature"><i class="bi bi-check-circle-fill"></i><span>Prioritet dəstək</span></div>@endif
                    <a href="{{ route('register') }}" class="{{ $p[4] ? 'btn-pkg-primary' : 'btn-pkg-outline' }}">Başla</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <p class="text-center mt-5" style="font-size:.84rem;color:var(--muted)">
            <i class="bi bi-shield-check me-1" style="color:var(--primary)"></i>
            Kredit kartı tələb olunmur &nbsp;·&nbsp; İstənilən vaxt ləğv edin &nbsp;·&nbsp; 14 gün pulsuz sınaq
        </p>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     TESTIMONIALS
══════════════════════════════════════════════ --}}
<section class="testimonials-section">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-tag" style="justify-content:center;margin:0 auto 14px">Rəylər</div>
            <h2 class="section-heading">Həkimlər nə deyir?</h2>
        </div>
        <div class="row g-4">
            @foreach([
                ['NM','Nigar M.','Diş Həkimi, Bakı','InnApp bizim klinikamızın iş prosesini tamamilə dəyişdi. Randevular indi heç vaxt üst-üstə düşmür, xəstələrə vaxtında SMS gedir.'],
                ['TH','Tural H.','Klinika direktoru','Ən çox xoşuma gələn cəhəti — sadəliyi. Köhnə işçilərimiz də 1 gündə sistemə alışdı. Hesabatlar çox faydalıdır.'],
                ['SQ','Sevinc Q.','Ortodontist','SMS xatırlatma xüsusiyyəti xəstə davamiyyətini 40% artırdı. İndi xəstələr randevularını unutmurlar.'],
            ] as $t)
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-quote">"</div>
                    <div class="testimonial-stars">★★★★★</div>
                    <p class="testimonial-text">{{ $t[3] }}</p>
                    <div class="testimonial-author">
                        <div class="t-avatar">{{ $t[0] }}</div>
                        <div>
                            <div class="t-name">{{ $t[1] }}</div>
                            <div class="t-role">{{ $t[2] }}</div>
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
    <div class="cta-glow g1"></div>
    <div class="cta-glow g2"></div>
    <div class="container text-center position-relative" style="z-index:2">
        <div class="cta-badge">
            <i class="bi bi-lightning-charge-fill"></i>
            Pulsuz başlayın
        </div>
        <h2 style="font-size:clamp(1.8rem,4vw,2.8rem);font-weight:800;color:#fff;line-height:1.2;letter-spacing:-.5px;margin-bottom:14px">
            Klinikanızı bu gün<br>rəqəmsallaşdırın
        </h2>
        <p style="color:rgba(255,255,255,.6);font-size:1rem;max-width:480px;margin:0 auto 34px;line-height:1.75">
            14 günlük pulsuz sınaq dövrü ilə riski sıfıra endirin. Kredit kartı tələb olunmur.
        </p>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <a href="{{ route('register') }}" class="btn-primary-hero">
                <i class="bi bi-rocket-takeoff-fill"></i>
                İndi qeydiyyatdan keç
            </a>
            <a href="{{ route('demo.start') }}" class="btn-demo-hero">
                <span class="live-dot"></span>
                Canlı demo
            </a>
        </div>
        <p class="mt-4" style="font-size:.82rem;color:rgba(255,255,255,.38)">
            Artıq hesabınız var? <a href="{{ route('login') }}" style="color:rgba(27,200,200,.8);text-decoration:none;font-weight:600">Daxil olun →</a>
        </p>
    </div>
</section>

{{-- Floating demo widget --}}
<div class="demo-float">
    <div class="demo-float-label">Pulsuz sınayın</div>
    <a href="{{ route('demo.start') }}" class="demo-float-btn">
        <span class="live-dot"></span>
        Canlı demo
    </a>
</div>

@endsection
