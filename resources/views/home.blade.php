@extends('layouts.public')

@section('title', 'InnApp | Stomatoloji klinikalar üçün idarəetmə sistemi')
@section('meta_description', 'InnApp stomatoloji klinikalar üçün randevu, xəstə bazası, SMS və hesabat idarəetməsi təqdim edir.')

@section('content')
<div class="banner-area content-double transparent-nav bg-gradient text-light small-text">
    <div class="box-table">
        <div class="box-cell">
            <div class="container">
                <div class="double-items">
                    <div class="row align-center">
                        <div class="col-lg-5 left-info simple-video">
                            <div class="content" data-animation="animated fadeInUpBig">
                                <h1>Klinikanızı bir paneldən idarə edin</h1>
                                <p>
                                    InnApp stomatoloji klinikalar üçün hazırlanmış modern idarəetmə sistemidir.
                                    Randevular, xəstə tarixçəsi, SMS bildirişləri və gəlir analitikası eyni platformada toplanır.
                                </p>
                                <a class="btn circle btn-light border btn-md" href="{{ route('register') }}">Pulsuz başla</a>
                            </div>
                        </div>
                        <div class="col-lg-7 right-info width-max">
                            <img src="{{ asset('assets/img/illustration/5.png') }}" alt="InnApp dashboard">
                        </div>
                    </div>
                </div>
            </div>
            <div class="wavesshape">
                <img src="{{ asset('assets/img/shape/2.png') }}" alt="Shape">
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
                        <h4>InnApp haqqında</h4>
                        <h2>Klinikanın gündəlik işini sadələşdirən sistem</h2>
                        <p>
                            Həkimlər və administratorlar üçün daha sürətli qeydiyyat, daha dəqiq planlama və daha rahat nəzarət.
                            Manual cədvəlləri və dağınıq qeydləri vahid rəqəmsal axına çevirin.
                        </p>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="top-features active-all">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 single-item">
                                <div class="item">
                                    <img src="{{ asset('assets/img/icon/1.svg') }}" alt="Randevu">
                                    <h4>Canlı randevu axını</h4>
                                    <p>Boş saatları görün, üst-üstə düşmələri azaldın və qəbul planını saniyələr içində qurun.</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 single-item">
                                <div class="item">
                                    <img src="{{ asset('assets/img/icon/2.svg') }}" alt="SMS">
                                    <h4>SMS workflow</h4>
                                    <p>Xatırlatma, təsdiq və məlumat mesajlarını şablonlarla avtomatlaşdırın.</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 single-item">
                                <div class="item">
                                    <img src="{{ asset('assets/img/icon/3.svg') }}" alt="Xəstə bazası">
                                    <h4>Xəstə kartoteki</h4>
                                    <p>Müalicə qeydləri, kontaktlar və vizit tarixçəsi hər xəstə üçün tam şəkildə saxlanılır.</p>
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
                    <img src="{{ asset('assets/img/illustration/6.png') }}" alt="Üstünlüklər">
                </div>
                <div class="col-lg-6 info">
                    <h5>Niyə InnApp</h5>
                    <h2>Klinikanız üçün real əməliyyat üstünlüyü yaradın</h2>
                    <p>
                        Admin işinin yükünü azaldın, həkimlərin qəbul ritmini qoruyun və rəhbərlik üçün ölçülə bilən nəticələr yaradın.
                        InnApp həm kiçik kabinetlər, həm də çox həkimli klinikalar üçün uyğundur.
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
                        Klinika idarəetməsinin gündəlik ehtiyaclarına uyğun qurulmuş funksiyalar.
                        Artıq sistemlər arasında keçid etmədən hər şeyi bir yerdən idarə edin.
                    </p>
                </div>
            </div>
        </div>
        <div class="features-items">
            <div class="row">
                @foreach([
                    ['fas fa-calendar-check', 'Randevu idarəetməsi', 'Günlük qəbul planını rahat qurun və hər həkimin iş saatını ayrıca idarə edin.'],
                    ['fas fa-sliders-h', 'Tam fərdiləşmə', 'Xidmət növləri, iş qrafiki, SMS mətni və proses axınını klinikanıza uyğunlaşdırın.'],
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
                    Qeydiyyatdan keçin, klinika parametrlərini qurun və ilk xəstə axınınızı eyni gün ərzində sistemə köçürün.
                </p>
                <ul>
                    <li>Hesab yaradın və klinika məlumatlarını əlavə edin</li>
                    <li>Həkim iş saatları, xidmətlər və SMS şablonlarını qurun</li>
                    <li>Randevuları yaradın və qəbul prosesini rəqəmsallaşdırın</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="faq-content">
                    <div class="accordion" id="setupAccordion">
                        @foreach([
                            ['Quraşdırma nə qədər çəkir?', 'Əksər klinikalar ilkin qurulmanı 10-20 dəqiqə ərzində tamamlayır.'],
                            ['Texniki bilik lazımdır?', 'Xeyr. İnterfeys klinika komandası üçün sadə və istifadəyə yönəlik hazırlanıb.'],
                            ['Demo versiya var?', 'Bəli. Sistemə keçmədən əvvəl demo hesab ilə əsas axını yoxlaya bilərsiniz.'],
                        ] as $index => $item)
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
                        Aktiv klinikanın ölçüsünə və komandaya uyğun çevik plan seçin.
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
                                    <h4>{{ $package->name }}</h4>
                                    <h2><sup>₼</sup>{{ number_format($package->price, 0) }} <sub>/ ay</sub></h2>
                                </li>
                                <li>{{ $package->description ?: 'Klinikanın gündəlik prosesləri üçün əsas funksiyalar.' }}</li>
                                <li>{{ $package->patient_limit ? $package->patient_limit . ' xəstə limiti' : 'Limitsiz xəstə' }}</li>
                                <li>{{ $package->sms_limit ? $package->sms_limit . ' SMS / ay' : 'Limitsiz SMS' }}</li>
                                <li>Randevu və təqvim idarəetməsi</li>
                                <li>Vizit və gəlir hesabatları</li>
                                <li class="footer">
                                    <a class="btn circle btn-theme border btn-sm" href="{{ route('register') }}">Seç</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                @empty
                    @foreach([
                        ['Start', '29', '50 xəstə limiti', '500 SMS / ay'],
                        ['Pro', '79', 'Limitsiz xəstə', '2000 SMS / ay'],
                        ['Klinika+', '149', 'Limitsiz xəstə', 'Limitsiz SMS'],
                    ] as $plan)
                        <div class="col-lg-4 col-md-6 single-item">
                            <div class="pricing-item">
                                <ul>
                                    <li class="pricing-header">
                                        <h4>{{ $plan[0] }}</h4>
                                        <h2><sup>₼</sup>{{ $plan[1] }} <sub>/ ay</sub></h2>
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
                <h2>Həkimlər nə deyir?</h2>
                <p>
                    Real klinika prosesinə uyğun qurulmuş sistem olduğuna görə istifadəçilər ilk gündən fərqi hiss edir.
                </p>
            </div>
            <div class="col-lg-7 testimonial-items">
                <div id="testimonial-carousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach([
                            ['InnApp ilə qəbul planımız daha nizamlı oldu. Xəstə axını qarışmır və administrator daha az vaxt sərf edir.', 'Nigar Məmmədova', 'Diş həkimi'],
                            ['SMS xatırlatmaları sayəsində buraxılan randevular ciddi şəkildə azalıb. Bu, birbaşa gəlirə təsir edir.', 'Tural Həsənov', 'Klinika rəhbəri'],
                            ['Yeni işçi gələndə sistemi öyrətmək çətin olmur. İnterfeys həqiqətən sadə qurulub.', 'Sevinc Quliyeva', 'Ortodontist'],
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
                            <img src="{{ asset('assets/img/team/4.jpg') }}" alt="Nigar">
                        </li>
                        <li data-bs-target="#testimonial-carousel" data-bs-slide-to="1">
                            <img src="{{ asset('assets/img/team/2.jpg') }}" alt="Tural">
                        </li>
                        <li data-bs-target="#testimonial-carousel" data-bs-slide-to="2">
                            <img src="{{ asset('assets/img/team/9.jpg') }}" alt="Sevinc">
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="subscribe-area bg-fixed shadow dark text-light default-padding text-center" style="background-image: url('{{ asset('assets/img/banner/4.jpg') }}');">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <h3>Demo üçün əlaqə saxlayın</h3>
                <p>
                    Klinikaya uyğun təqdimat, demo keçid və ilkin qurulum istiqaməti üçün email ünvanınızı paylaşın.
                </p>
                <div class="subscribe">
                    <form action="#" onsubmit="return false;">
                        <div class="input-group stylish-input-group">
                            <input type="email" placeholder="Email ünvanınızı yazın" class="form-control" name="email">
                            <span class="input-group-addon">
                                <button type="submit">
                                    <i class="fa fa-paper-plane"></i>
                                </button>
                            </span>
                        </div>
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
                            <span>Bakı, Azərbaycan<br>Stomatoloji klinikalar üçün SaaS platforma</span>
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
                    <div class="social-address">
                        <h4>Sosial şəbəkələr</h4>
                        <ul class="social">
                            <li class="facebook"><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                            <li class="twitter"><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                            <li class="instagram"><a href="#"><i class="fab fa-instagram"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 contact-form">
                <h2>Klinikanız üçün təqdimat istəyin</h2>
                <form action="#" class="contact-form" onsubmit="return false;">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <input class="form-control" placeholder="Ad və soyad" type="text">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <input class="form-control" placeholder="Email" type="email">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <input class="form-control" placeholder="Telefon" type="text">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group comments">
                                <textarea class="form-control" placeholder="Klinikanız haqqında qısa məlumat"></textarea>
                            </div>
                        </div>
                    </div>
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
                        @foreach([
                            ['Sistem buluddadır?', 'Bəli. InnApp bulud əsaslı işləyir və lokal server qurulumu tələb etmir.'],
                            ['Mobil cihazdan istifadə mümkündür?', 'Bəli. Sistem telefon və planşetdə də açılır və əsas əməliyyatlar rahat idarə olunur.'],
                            ['SMS paketə daxildir?', 'Paketdən asılı olaraq aylıq SMS limiti təqdim olunur, daha böyük planlarda limit daha yüksəkdir.'],
                            ['Məlumatlar təhlükəsiz saxlanılır?', 'İstifadəçi girişi, rol əsaslı icazələr və mərkəzləşdirilmiş idarəetmə ilə məlumat axını nəzarətdə saxlanılır.'],
                        ] as $index => $faq)
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
                <img src="{{ asset('assets/img/banner/contact.png') }}" alt="Əlaqə">
            </div>
        </div>
    </div>
</div>
@endsection
