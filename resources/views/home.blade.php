@extends('layouts.public')

@section('title', 'InnApp | Randevu idarəetmə sistemi')
@section('meta_description', 'InnApp müxtəlif sahələr üçün randevu, müştəri bazası, SMS və hesabat idarəetməsi təqdim edir.')

@php
    // Shared by the accordions below and the FAQPage schema so the two never drift apart.
    $setupFaqs = [
        ['Quraşdırma nə qədər çəkir?', 'Əksər müəssisələr ilkin qurulmanı 10-20 dəqiqə ərzində tamamlayır.'],
        ['Texniki bilik lazımdır?', 'Xeyr. İnterfeys komanda üçün sadə və istifadəyə yönəlik hazırlanıb.'],
        ['Demo versiya var?', 'Bəli. Sistemə keçmədən əvvəl demo hesab ilə əsas axını yoxlaya bilərsiniz.'],
    ];
    $generalFaqs = [
        ['Sistem buluddadır?', 'Bəli. InnApp bulud əsaslı işləyir və lokal server qurulumu tələb etmir.'],
        ['Mobil cihazdan istifadə mümkündür?', 'Bəli. Sistem telefon və planşetdə də açılır və əsas əməliyyatlar rahat idarə olunur.'],
        ['SMS paketə daxildir?', 'Paketdən asılı olaraq aylıq SMS limiti təqdim olunur, daha böyük planlarda limit daha yüksəkdir.'],
        ['Məlumatlar təhlükəsiz saxlanılır?', 'İstifadəçi girişi, rol əsaslı icazələr və mərkəzləşdirilmiş idarəetmə ilə məlumat axını nəzarətdə saxlanılır.'],
    ];
@endphp

@push('schema')
@php
    $siteUrl = rtrim(config('app.url'), '/');

    $offers = $packages->map(fn ($package) => [
        '@type' => 'Offer',
        'name' => $package->name,
        'price' => number_format($package->price_per_seat, 2, '.', ''),
        'priceCurrency' => 'AZN',
        'priceSpecification' => [
            '@type' => 'UnitPriceSpecification',
            'price' => number_format($package->price_per_seat, 2, '.', ''),
            'priceCurrency' => 'AZN',
            'unitText' => 'əməkdaş / ay',
            'billingIncrement' => 1,
            'referenceQuantity' => [
                '@type' => 'QuantitativeValue',
                'value' => 1,
                'unitCode' => 'MON',
            ],
        ],
        'availability' => 'https://schema.org/InStock',
        'url' => route('register'),
    ])->values()->all();

    $homeSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            array_filter([
                '@type' => 'SoftwareApplication',
                '@id' => $siteUrl . '/#software',
                'name' => 'InnApp',
                'url' => $siteUrl . '/',
                'description' => 'Xidmət sahəsində fəaliyyət göstərən müəssisələr üçün randevu, müştəri bazası, SMS/WhatsApp bildirişləri və hesabat idarəetmə sistemi.',
                'applicationCategory' => 'BusinessApplication',
                'applicationSubCategory' => 'Appointment scheduling',
                'operatingSystem' => 'Web',
                'inLanguage' => 'az',
                'isAccessibleForFree' => false,
                'featureList' => [
                    'Randevu və təqvim idarəetməsi',
                    'Müştəri bazası',
                    'SMS və WhatsApp bildirişləri',
                    'Vizit və gəlir hesabatları',
                    'Çox əməkdaşlı müəssisə paneli',
                ],
                'publisher' => ['@id' => $siteUrl . '/#organization'],
                'offers' => $offers ?: null,
            ], fn ($value) => $value !== null),
            [
                '@type' => 'FAQPage',
                '@id' => $siteUrl . '/#faq',
                'mainEntity' => collect($setupFaqs)->merge($generalFaqs)->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq[0],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq[1],
                    ],
                ])->values()->all(),
            ],
            [
                '@type' => 'WebPage',
                '@id' => $siteUrl . '/#webpage',
                'url' => $siteUrl . '/',
                'name' => 'InnApp | Randevu idarəetmə sistemi',
                'description' => 'InnApp müxtəlif sahələr üçün randevu, müştəri bazası, SMS və hesabat idarəetməsi təqdim edir.',
                'inLanguage' => 'az',
                'isPartOf' => ['@id' => $siteUrl . '/#website'],
                'about' => ['@id' => $siteUrl . '/#software'],
                'primaryImageOfPage' => [
                    '@type' => 'ImageObject',
                    'url' => asset('assets/img/og/innapp-1200x630.png'),
                    'width' => 1200,
                    'height' => 630,
                ],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">@json($homeSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
@endpush

@section('content')
<div class="banner-area content-double transparent-nav bg-gradient text-light small-text">
    <div class="box-table">
        <div class="box-cell">
            <div class="container">
                <div class="double-items">
                    <div class="row align-center">
                        <div class="col-lg-5 left-info simple-video">
                            <div class="content">
                                <h1>İşinizi bir paneldən idarə edin</h1>
                                <p>
                                    InnApp müxtəlif sahələr üçün hazırlanmış modern randevu idarəetmə sistemidir.
                                    Randevular, müştəri tarixçəsi, SMS bildirişləri və gəlir analitikası eyni platformada toplanır.
                                </p>
                                <a class="btn circle btn-light border btn-md" href="{{ route('register') }}">Pulsuz başla</a>
                            </div>
                        </div>
                        <div class="col-lg-7 right-info width-max">
                            <img src="{{ asset('assets/img/illustration/5.png') }}" alt="InnApp idarəetmə panelinin nümunə görünüşü" width="847" height="484" fetchpriority="high">
                        </div>
                    </div>
                </div>
            </div>
            <div class="wavesshape">
                <img src="{{ asset('assets/img/shape/2.png') }}" alt="" aria-hidden="true" width="1900" height="1000">
            </div>
        </div>
    </div>
</div>

<div id="about" class="about-area default-padding-top">
    <div class="container">
        <div class="row">
            <div class="about-items text-center">
                <div class="col-lg-8 offset-lg-2">
                    <div class="about-content text-center">
                        <p class="eyebrow">InnApp haqqında</p>
                        <h2>İşinizin gündəlik axınını sadələşdirən sistem</h2>
                        <p>
                            Mütəxəssislər və administratorlar üçün daha sürətli qeydiyyat, daha dəqiq planlama və daha rahat nəzarət.
                            Manual cədvəlləri və dağınıq qeydləri vahid rəqəmsal axına çevirin.
                        </p>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="top-features active-all">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 single-item">
                                <div class="item">
                                    <img src="{{ asset('assets/img/icon/1.svg') }}" alt="" width="80" height="80" loading="lazy">
                                    <h4>Canlı randevu axını</h4>
                                    <p>Boş saatları görün, üst-üstə düşmələri azaldın və qəbul planını saniyələr içində qurun.</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 single-item">
                                <div class="item">
                                    <img src="{{ asset('assets/img/icon/2.svg') }}" alt="" width="80" height="80" loading="lazy">
                                    <h4>SMS workflow</h4>
                                    <p>Xatırlatma, təsdiq və məlumat mesajlarını şablonlarla avtomatlaşdırın.</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 single-item">
                                <div class="item">
                                    <img src="{{ asset('assets/img/icon/3.svg') }}" alt="" width="80" height="80" loading="lazy">
                                    <h4>Müştəri kartoteki</h4>
                                    <p>Xidmət qeydləri, kontaktlar və vizit tarixçəsi hər müştəri üçün tam şəkildə saxlanılır.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="overview" class="choseus-area default-padding bg-theme-small">
    <div class="container">
        <div class="choseus-items">
            <div class="row align-center">
                <div class="col-lg-6 thumb pr-80 pr-md-15 pr-xs-15">
                    <img src="{{ asset('assets/img/illustration/6.png') }}" alt="InnApp təqvim və randevu axını" width="800" height="614" loading="lazy">
                </div>
                <div class="col-lg-6 info">
                    <p class="eyebrow">Niyə InnApp</p>
                    <h2>İşiniz üçün real əməliyyat üstünlüyü yaradın</h2>
                    <p>
                        Admin işinin yükünü azaldın, mütəxəssislərin qəbul ritmini qoruyun və rəhbərlik üçün ölçülə bilən nəticələr yaradın.
                        InnApp həm kiçik kabinetlər, həm də çox mütəxəssisli müəssisələr üçün uyğundur.
                    </p>
                    <a class="btn circle btn-theme border btn-md" href="{{ route('demo.start') }}">Canlı demo gör</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="features" class="features-area default-padding bottom-small">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="site-heading text-center">
                    <h2>Əsas imkanlar</h2>
                    <p>
                        İdarəetmənin gündəlik ehtiyaclarına uyğun qurulmuş funksiyalar.
                        Artıq sistemlər arasında keçid etmədən hər şeyi bir yerdən idarə edin.
                    </p>
                </div>
            </div>
        </div>
        <div class="features-items">
            <div class="row">
                @foreach([
                    ['fas fa-calendar-check', 'Randevu idarəetməsi', 'Günlük qəbul planını rahat qurun və hər mütəxəssisin iş saatını ayrıca idarə edin.'],
                    ['fas fa-sliders-h', 'Tam fərdiləşmə', 'Xidmət növləri, iş qrafiki, SMS mətni və proses axınını müəssisənizə uyğunlaşdırın.'],
                    ['fas fa-sms', 'SMS inteqrasiyası', 'Təsdiq və xatırlatma mesajlarını avtomatik göndərin.'],
                    ['fas fa-notes-medical', 'Vizit tarixçəsi', 'Keçmiş prosedurlar, qeydlər və faylları xəstə kartında saxlayın.'],
                    ['fas fa-chart-line', 'Hesabatlar', 'Gəlir, aktivlik və istifadə statistikasını aylıq və həftəlik izləyin.'],
                    ['fas fa-box-open', 'Abunəlik nəzarəti', 'Paketlər, limitlər və ödəniş statuslarını sistem daxilindən görün.'],
                ] as $feature)
                    <div class="col-lg-4 col-md-6 single-item">
                        <div class="item">
                            <div class="icon">
                                <i class="{{ $feature[0] }}"></i>
                            </div>
                            <div class="info">
                                <h4>{{ $feature[1] }}</h4>
                                <p>{{ $feature[2] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="default-padding bg-gray">
    <div class="container">
        <div class="row align-center">
            <div class="col-lg-6 info">
                <h2>3 addımda işə başlayın</h2>
                <p>
                    Qeydiyyatdan keçin, iş parametrlərini qurun və ilk müştəri axınınızı eyni gün ərzində sistemə köçürün.
                </p>
                <ul>
                    <li>Hesab yaradın və müəssisə məlumatlarını əlavə edin</li>
                    <li>Mütəxəssis iş saatları, xidmətlər və SMS şablonlarını qurun</li>
                    <li>Randevuları yaradın və qəbul prosesini rəqəmsallaşdırın</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="faq-content">
                    <div class="accordion" id="setupAccordion">
                        @foreach($setupFaqs as $index => $item)
                            <div class="accordion-item card">
                                <div class="accordion-header card-header" id="setupHeading{{ $index }}">
                                    <button class="accordion-button {{ $index ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#setupCollapse{{ $index }}" aria-expanded="{{ $index ? 'false' : 'true' }}" aria-controls="setupCollapse{{ $index }}">
                                        {{ $item[0] }}
                                    </button>
                                </div>
                                <div id="setupCollapse{{ $index }}" class="accordion-collapse collapse {{ $index ? '' : 'show' }}" aria-labelledby="setupHeading{{ $index }}" data-bs-parent="#setupAccordion">
                                    <div class="card-body">
                                        <p>{{ $item[1] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="pricing" class="pricing-area default-padding-top bottom-less">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="site-heading text-center">
                    <h2>Paketlər</h2>
                    <p>
                        Müəssisənizin ölçüsünə və komandaya uyğun çevik plan seçin.
                        Paketləri sonradan dəyişmək mümkündür.
                    </p>
                </div>
            </div>
        </div>
        <div class="pricing pricing-simple text-center">
            <div class="row">
                @forelse($packages as $package)
                    <div class="col-lg-4 col-md-6 single-item">
                        <div class="pricing-item">
                            <ul>
                                <li class="pricing-header">
                                    <h3>{{ $package->name }}</h3>
                                    <div class="price"><sup>₼</sup>{{ number_format($package->price_per_seat, 0) }} <sub>/ əməkdaş / ay</sub></div>
                                </li>
                                <li>{{ $package->description ?: 'Tək mütəxəssis üçün də, klinika üçün də.' }}</li>
                                <li>{{ $package->patient_limit ? $package->patient_limit . ' müştəri limiti' : 'Limitsiz müştəri' }}</li>
                                <li>Limitsiz SMS və WhatsApp</li>
                                <li>Randevu və təqvim idarəetməsi</li>
                                <li>Vizit və gəlir hesabatları</li>
                                <li>İstədiyiniz qədər əməkdaş əlavə edin</li>
                                <li class="footer">
                                    <a class="btn circle btn-theme border btn-sm" href="{{ route('register') }}">Seç</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                @empty
                    @foreach([
                        ['Start', '29', '50 müştəri limiti', '500 SMS / ay'],
                        ['Pro', '79', 'Limitsiz müştəri', '2000 SMS / ay'],
                        ['Pro+', '149', 'Limitsiz müştəri', 'Limitsiz SMS'],
                    ] as $plan)
                        <div class="col-lg-4 col-md-6 single-item">
                            <div class="pricing-item">
                                <ul>
                                    <li class="pricing-header">
                                        <h3>{{ $plan[0] }}</h3>
                                        <div class="price"><sup>₼</sup>{{ $plan[1] }} <sub>/ ay</sub></div>
                                    </li>
                                    <li>{{ $plan[2] }}</li>
                                    <li>{{ $plan[3] }}</li>
                                    <li>Randevu və təqvim idarəetməsi</li>
                                    <li>Vizit və gəlir hesabatları</li>
                                    <li>SMS bildirişləri</li>
                                    <li class="footer">
                                        <a class="btn circle btn-theme border btn-sm" href="{{ route('register') }}">Seç</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="testimonials-area default-padding bg-gray">
    <div class="container">
        <div class="row align-center">
            <div class="col-lg-5 info">
                <h2>İstifadəçilər nə deyir?</h2>
                <p>
                    Real iş prosesinə uyğun qurulmuş sistem olduğuna görə istifadəçilər ilk gündən fərqi hiss edir.
                </p>
            </div>
            <div class="col-lg-7 testimonial-items">
                <div id="testimonial-carousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach([
                            ['InnApp ilə qəbul planımız daha nizamlı oldu. Müştəri axını qarışmır və administrator daha az vaxt sərf edir.', 'Nigar Məmmədova', 'Mütəxəssis'],
                            ['SMS xatırlatmaları sayəsində buraxılan randevular ciddi şəkildə azalıb. Bu, birbaşa gəlirə təsir edir.', 'Tural Həsənov', 'Müəssisə rəhbəri'],
                            ['Yeni işçi gələndə sistemi öyrətmək çətin olmur. İnterfeys həqiqətən sadə qurulub.', 'Sevinc Quliyeva', 'Mütəxəssis'],
                        ] as $index => $testimonial)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <div class="item">
                                    <p>{{ $testimonial[0] }}</p>
                                    <h4>{{ $testimonial[1] }}</h4>
                                    <span>{{ $testimonial[2] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <ol class="carousel-indicators">
                        <li data-bs-target="#testimonial-carousel" data-bs-slide-to="0" class="active" aria-current="true">
                            <img src="{{ asset('assets/img/team/4.jpg') }}" alt="Nigar Məmmədova" width="60" height="60" loading="lazy">
                        </li>
                        <li data-bs-target="#testimonial-carousel" data-bs-slide-to="1">
                            <img src="{{ asset('assets/img/team/2.jpg') }}" alt="Tural Həsənov" width="60" height="60" loading="lazy">
                        </li>
                        <li data-bs-target="#testimonial-carousel" data-bs-slide-to="2">
                            <img src="{{ asset('assets/img/team/9.jpg') }}" alt="Sevinc Quliyeva" width="60" height="60" loading="lazy">
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="promoters" class="promoter-area features-area default-padding">
    <div class="container">
        <div class="row align-center">
            <div class="col-lg-6 info">
                <p class="eyebrow">Tərəfdaşlıq proqramı</p>
                <h2>Promotor olun, hər satışdan qazanın</h2>
                <p>
                    InnApp-i tanıdığınız müəssisələrə tövsiyə edin. Qeydiyyatdan keçən kimi şəxsi promo kodunuz
                    avtomatik yaradılır — müştəriləriniz endirim qazanır, siz isə onların hər uğurlu ödənişindən
                    komissiya əldə edirsiniz.
                </p>
                <ul>
                    <li>Şəxsi promo kod — qeydiyyatdan dərhal sonra avtomatik</li>
                    <li>Müştəriləriniz üçün {{ rtrim(rtrim(number_format($promoDiscount ?? 20, 2), '0'), '.') }}% ilkin qeydiyyat endirimi</li>
                    <li>Hər ödənişdən {{ rtrim(rtrim(number_format($promoCommission ?? 5, 2), '0'), '.') }}% davamlı komissiya</li>
                    <li>Qazanclarınızı öz panelinizdən real vaxtda izləyin</li>
                </ul>
                <a class="btn circle btn-theme border btn-md" href="{{ route('promoter.register') }}">Promotor kimi qeydiyyat</a>
            </div>
            <div class="col-lg-6">
                <div class="features-items">
                    <div class="row">
                        <div class="col-md-6 single-item">
                            <div class="item">
                                <div class="icon">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <div class="info">
                                    <h4>{{ rtrim(rtrim(number_format($promoDiscount ?? 20, 2), '0'), '.') }}% endirim</h4>
                                    <p>Kodunuzla qeydiyyatdan keçən hər müştəri ilk ödənişində endirim qazanır.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 single-item">
                            <div class="item">
                                <div class="icon">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <div class="info">
                                    <h4>{{ rtrim(rtrim(number_format($promoCommission ?? 5, 2), '0'), '.') }}% komissiya</h4>
                                    <p>Müştərilərinizin hər uğurlu ödənişindən hesabınıza komissiya yazılır.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="demo" class="subscribe-area shadow dark text-light default-padding text-center" style="background-image: url('{{ asset('assets/img/banner/4.jpg') }}');">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <h3>Demo üçün əlaqə saxlayın</h3>
                <p>
                    İşinizə uyğun təqdimat, demo keçid və ilkin qurulum istiqaməti üçün email ünvanınızı paylaşın.
                </p>
                <div class="subscribe">
                    @if (session('inquiry_sent') === \App\Models\Inquiry::TYPE_DEMO)
                        <div class="form-notice form-notice--success" role="status">
                            <i class="fa fa-check-circle"></i> Təşəkkürlər! Sorğunuz qəbul edildi, tezliklə sizinlə əlaqə saxlayacağıq.
                        </div>
                    @endif
                    <form action="{{ route('inquiry.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="{{ \App\Models\Inquiry::TYPE_DEMO }}">
                        <input type="text" name="website" value="" class="form-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                        <div class="input-group stylish-input-group">
                            <input type="email" placeholder="Email ünvanınızı yazın" class="form-control" name="email" value="{{ $errors->demo->any() ? old('email') : '' }}" required aria-label="E-poçt ünvanı">
                            <span class="input-group-addon">
                                <button type="submit" aria-label="Göndər">
                                    <i class="fa fa-paper-plane"></i>
                                </button>
                            </span>
                        </div>
                        @if ($errors->demo->any())
                            <div class="form-notice form-notice--error" role="alert">{{ $errors->demo->first() }}</div>
                        @endif
                        @include('auth._turnstile', ['form' => 'inquiry'])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="contact" class="contact-us-area default-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="site-heading text-center">
                    <h2>Əlaqə</h2>
                    <p>
                        Demo sifarişi, satış sualları və ya sistemlə bağlı dəstək ehtiyacları üçün bizimlə əlaqə saxlayın.
                    </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 address">
                <div class="address-items">
                    <h4>Bizim əlaqə məlumatlarımız</h4>
                    <ul class="info">
                        <li>
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Bakı, Azərbaycan<br>Randevu idarəetməsi üçün SaaS platforma</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>+994 55 703 80 08</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope-open"></i>
                            <span>info@innapp.az</span>
                        </li>
                    </ul>
                    @if ($socialLinks = array_filter(config('services.social', [])))
                    <div class="social-address">
                        <h4>Sosial şəbəkələr</h4>
                        <ul class="social">
                            @foreach ($socialLinks as $network => $url)
                                <li class="{{ $network }}">
                                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ucfirst($network) }}">
                                        <i class="fab fa-{{ ['facebook' => 'facebook-f', 'linkedin' => 'linkedin-in'][$network] ?? $network }}"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-6 contact-form">
                <h2>Müəssisəniz üçün təqdimat istəyin</h2>
                @if (session('inquiry_sent') === \App\Models\Inquiry::TYPE_CONTACT)
                    <div class="form-notice form-notice--success" role="status">
                        <i class="fa fa-check-circle"></i> Təşəkkürlər! Sorğunuz qəbul edildi, tezliklə sizinlə əlaqə saxlayacağıq.
                    </div>
                @endif
                @php($contactOld = fn ($field) => $errors->contact->any() ? old($field) : '')
                <form action="{{ route('inquiry.store') }}" method="POST" class="contact-form">
                    @csrf
                    <input type="hidden" name="type" value="{{ \App\Models\Inquiry::TYPE_CONTACT }}">
                    <input type="text" name="website" value="" class="form-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <input class="form-control" placeholder="Ad və soyad" type="text" name="name" value="{{ $contactOld('name') }}" required maxlength="120" aria-label="Ad və soyad">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <input class="form-control" placeholder="Email" type="email" name="email" value="{{ $contactOld('email') }}" maxlength="150" aria-label="E-poçt">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <input class="form-control" placeholder="Telefon (05X XXX XX XX)" type="tel" name="phone" value="{{ $contactOld('phone') }}" maxlength="20" aria-label="Telefon">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group comments">
                                <textarea class="form-control" placeholder="Müəssisəniz haqqında qısa məlumat" name="message" maxlength="2000" aria-label="Mesaj">{{ $contactOld('message') }}</textarea>
                            </div>
                        </div>
                    </div>
                    @if ($errors->contact->any())
                        <div class="form-notice form-notice--error" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->contact->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @include('auth._turnstile', ['form' => 'inquiry'])
                    <div class="row">
                        <div class="col-lg-12">
                            <button type="submit">
                                Sorğu göndər <i class="fa fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="faq-area bg-gray default-padding-top">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="site-heading text-center">
                    <h2>Tez-tez verilən suallar</h2>
                    <p>
                        Qərar verməzdən əvvəl ən çox verilən sualları bir yerdə topladıq.
                    </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 faq-items default-padding-bottom order-lg-last">
                <div class="faq-content">
                    <div class="accordion" id="accordionExample">
                        @foreach($generalFaqs as $index => $faq)
                            <div class="accordion-item card">
                                <div class="accordion-header card-header" id="heading{{ $index }}">
                                    <button class="accordion-button {{ $index ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $index ? 'false' : 'true' }}" aria-controls="collapse{{ $index }}">
                                        {{ $faq[0] }}
                                    </button>
                                </div>
                                <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index ? '' : 'show' }}" aria-labelledby="heading{{ $index }}" data-bs-parent="#accordionExample">
                                    <div class="card-body">
                                        <p>{{ $faq[1] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-6 thumb">
                <img src="{{ asset('assets/img/banner/contact.png') }}" alt="Təqdimat sorğusu" width="450" height="576" loading="lazy">
            </div>
        </div>
    </div>
</div>
@endsection
