<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="robots" content="index, follow">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="language" content="Indonesian">
    <meta name="author" content="Trijaya Pratama Futures" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>TPFx - {{ $title }}</title>
    <!--====== Favicon Icon ======-->
    <link rel="icon" type="image/png" href="{{ asset('public/assets/img/favicon-32x32.png') }}">
    <!-- all css -->
    <link rel="stylesheet" href="{{ asset('public/assets/landio/css/all.min.css') }}" media="all" defer />

	<meta name="facebook-domain-verification" content="k2t74lji55onaxfytfy01xhvxupfbv" />
    <!-- Google Tag Manager -->
    <script async>
        (function (w, d, s, l, i) { w[l] = w[l] || []; w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' }); var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.media="all" = true; j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);})(window, document, 'script', 'dataLayer', 'GTM-WWQKGDV');
    </script>
    <script media="all" src="https://www.googletagmanager.com/gtag/js?id=GTM-WWQKGDV" async></script>
    <script async>
        window.dataLayer = window.dataLayer || []; function gtag() {dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'GTM-WWQKGDV');
    </script>
    <!-- Twitter universal website tag code -->
    <script async>
        ! function (e, t, n, s, u, a) {e.twq || (s = e.twq = function () {s.exe ? s.exe.apply(s, arguments) : s.queue.push(arguments); }, s.version = '1.1', s.queue = [], u = t.createElement(n), u.media="all" = !0, u.src = '//static.ads-twitter.com/uwt.js', a = t.getElementsByTagName(n)[0], a.parentNode.insertBefore(u, a)) }(window, document, 'script'); twq('init', 'o82y0'); twq('track', 'PageView');
    </script>
    <!-- End Twitter universal website tag code -->
    <!-- Mgid Sensor -->
    <script type="text/javascript" async> (function() { var d = document, w = window; w.MgSensorData = w.MgSensorData || []; w.MgSensorData.push({ cid:712553, lng:"us", project: "a.mgid.com" }); var l = "a.mgid.com"; var n = d.getElementsByTagName("script")[0]; var s = d.createElement("script"); s.type = "text/javascript"; s.media="all" = true; var dt = !Date.now?new Date().valueOf():Date.now(); s.src = "https://" + l + "/mgsensor.js?d=" + dt; n.parentNode.insertBefore(s, n); })();>
    </script>
    <!-- /Mgid Sensor -->
    <script data-obct type="text/javascript" async>
        !function(_window, _document) {var OB_ADV_ID = '0069545528e42078ca6ee9d3c5ce1cb5e9';if (_window.obApi) {var toArray = function(object) {return Object.prototype.toString.call(object) === '[object Array]' ? object : [object];};_window.obApi.marketerId = toArray(_window.obApi.marketerId).concat(toArray(OB_ADV_ID));return;}var api = _window.obApi = function() {api.dispatch ? api.dispatch.apply(api, arguments) : api.queue.push(arguments);};api.version = '1.1';api.loaded = true;api.marketerId = OB_ADV_ID;api.queue = [];var tag = _document.createElement('script');tag.media="all" = true;tag.src = '//amplify.outbrain.com/cp/obtp.js';tag.type = 'text/javascript';var script = _document.getElementsByTagName('script')[0]; script.parentNode.insertBefore(tag, script);}(window, document); obApi('track', 'PAGE_VIEW');
    </script>
</head>

<body class="e-wallet-landing">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WWQKGDV" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WWQKGDV" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!--====== Start Header ======-->
    <header class="template-header navbar-center absolute-header nav-border-bottom sticky-header">
        <div class="container-fluid">
            <div class="header-inner">
                <div class="header-left">
                    <div class="branding-and-language-selection branding-border-right">
                        <div class="brand-logo">
                            <a href="https://tpfx.co.id">
                                <img src="{{ asset('public/assets/img/tpfx/logo7.png') }}" alt="logo tpfx">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!--====== End Header ======-->
    <!--====== Start Hero Area ======-->
    <section class="hero-area-v1">

        <div class="container-fluid1">
            <div class="image">
                @isset ($banner_image_desktop)
                <div class="w-100 d-none d-md-block">
                    <img class="w-100" src="/public/assets/img/azwqy-7sdei.avif" />
                </div>
                @endisset
                @isset ($banner_image_mobile)
                <div class="w-100 d-block d-md-none">
                    <img class="w-100" src="{{ asset('public/assets/img/' . $banner_image_mobile) }}" />
                </div>
                @endisset
            </div>
        </div>
    </section>
    <!--====== End Hero Area ======-->

    <!--====== Booking Form Start ======-->
    <section class="booking-section p-t-50 p-b-50">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-lg-6 col-md-10">
                            <div class="booking-text pr-xl-3 p-b-md-50">
                                <div class="common-heading-2 m-b-30">
                                    <h1 class="title" style="text-align: center;">{{ $section_1_heading }}</h1>
                                </div>
                                <h4>{{ $section_1_subheading }}</h4>
                                @isset($reward_cashback_tnc)
                                <a href="#tnc_cashback20reward">
                                    <h5 class="text-white">*syarat dan ketentuan berlaku</h5>
                                </a>
                                @endisset
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="booking-form wow fadeInUp ml-xl-3">
                                <h4 class="form-title">{{ $form_heading }}</h4>
                                <p>{{ $form_subheading }}</p>
                                <form id="leadsForm" role="form" method="POST"
                                    action="{{ route('events.store_leads') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="name">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="Enter full name" pattern="^[a-zA-Z\s.]+$" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="text" class="form-control" id="phone" aria-describedby="phoneHelp"
                                            name="phonenumber" placeholder="Enter phone number"
                                            pattern="(08|628)\d{10,14}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email address</label>
                                        <input type="email" class="form-control" id="email" aria-describedby="emailHelp"
                                            name="email" placeholder="Enter email address"
                                            pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
                                    </div>
                                    <input name="source" value="{{ $source }}" type="hidden" />
                                    <input name="status" value="344210" type="hidden" />
                                    <input name="utm_campaign" value="{{ $utm_campaign }}" type="hidden" />
                                    <button id="leadsFormSubmit" type="submit" class="d-none"></button>
                                    <div class="text-center">
                                        <button class="g-recaptcha btn btn-lg btn-warning"
                                            data-sitekey="6LcwJpQeAAAAAEsB9mnzuItB94EZqHIXV2eVjTxt"
                                            data-callback='onSubmit' data-action='submit'>
                                            <span class="btn-text">{{ $download_button }}</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--====== Booking Form End ======-->

    @if (isset($reward_cashback_tnc) && $source == 353627)
    <section>
        <div class="container py-5">
            <h2 id="tnc_cashback20reward" class="text-center fw-bold">Ramadan Promo Cashback 20% Reward</h2>
            </br>
            <p>Dapatkan Cashback Reward Program dengan membuka akun real di TPFx! Transaksi sebanyak-banyaknya dan Raih
                cashback hingga 20% ($1000) tanpa diundi. Cairkan bonus cashback langsung ke rekening kamu.
            </p>
            </br> </br>
            <h3>Periode Program</h3>
            <p>Periode Pendaftaran 8 April - 31 Mei 2022</p>
            <p>Periode Transaksi Hingga 31 Juli 2022</p>
            </br>
            <h3>Syarat & Ketentuan Umum</h3>
            <p>1) Periode Promosi Program berlaku mulai 8 April hingga 31 Mei 2022</p>
            <p>2) Deposit Requirement & Reward Emas sebagai berikut:</p>
            </br>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Deposit Min</th>
                        <th>Cashback %</th>
                        <th>Reward</th>
                        <th>Duration (month)</th>
                        <th>Lot Requirement</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>500</td>
                        <td>20%</td>
                        <td>$100</td>
                        <td>1</td>
                        <td>15</td>
                    </tr>
                    <tr>
                        <td>1000</td>
                        <td>20%</td>
                        <td>$200</td>
                        <td>1</td>
                        <td>30</td>
                    </tr>
                    <tr>
                        <td>2000</td>
                        <td>15%</td>
                        <td>$300</td>
                        <td>1</td>
                        <td>45</td>
                    </tr>
                    <tr>
                        <td>3000</td>
                        <td>15%</td>
                        <td>$450</td>
                        <td>1</td>
                        <td>60</td>
                    </tr>
                    <tr>
                        <td>4000</td>
                        <td>12%</td>
                        <td>$480</td>
                        <td>1</td>
                        <td>75</td>
                    </tr>
                    <tr>
                        <td>5000</td>
                        <td>12%</td>
                        <td>$600</td>
                        <td>1</td>
                        <td>90</td>
                    </tr>
                    <tr>
                        <td>6000</td>
                        <td>11%</td>
                        <td>$660</td>
                        <td>1</td>
                        <td>105</td>
                    </tr>
                    <tr>
                        <td>7000</td>
                        <td>11%</td>
                        <td>$770</td>
                        <td>1</td>
                        <td>120</td>
                    </tr>
                    <tr>
                        <td>8000</td>
                        <td>10%</td>
                        <td>$800</td>
                        <td>2</td>
                        <td>135</td>
                    </tr>
                    <tr>
                        <td>9000</td>
                        <td>10%</td>
                        <td>$900</td>
                        <td>2</td>
                        <td>150</td>
                    </tr>
                    <tr>
                        <td>10000</td>
                        <td>10%</td>
                        <td>$1000</td>
                        <td>2</td>
                        <td>175</td>
                    </tr>
                </tbody>
            </table></br>
            <p>3) Program berlaku untuk nasabah yang melakukan pembukaan akun real baru di TPFx, mendaftar dan melakukan
                verifikasi untuk mengikuti Cashback Reward Program.
            </p>
            <p>4) Peserta pada program Cashback harus berusia 21 tahun atau lebih.
            </p>
            <p>5) Hanya berlaku untuk Forex, Metal, Energy dan Indices.</p>
            <p>6) Program ini berlaku untuk semua Account Type.
            </p>
            <p>7) Kriteria pemenang cashback adalah berdasarkan nilai deposit awal dan total jumlah lot transaksi selama
                periode berlangsung.</p>
            <p>8) Setiap Nasabah hanya berhak mengklaim satu reward sesuai dengan jumlah lot transaksi yang dicapai.</p>
            <p>9) Jika nasabah tidak mencapai lot transaksi berdasarkan deposit awal, maka nasabah tetap bisa mengklaim
                reward sesuai dengan pencapaian lot transaksi.</p>
            <p>10) Peserta harus melakukan transaksi mandiri dan tidak dapat diwakilkan melalui sebuah kelompok, copy
                trade, robot trading, trade balance, arbitrase dan sebagainya.</p>
            <p>11) Peserta wajib melakukan klaim reward melalui form yang sudah ditentukan.</p>
            <p>12) Batas akhir klaim reward tanggal 5 Agustus 2022 (Semua klaim setelah tanggal tersebut tidak
                berlaku).</p>
            <p>13) Cashback Reward akan dicairkan langsung ke rekening pemenang yang sudah didaftarkan di TPFx.
            </p>
            <p>14) Pajak hadiah ditanggung oleh pemenang</p>
            <p>15) Cashback hanya akan diberikan jika peserta memenuhi syarat minimum margin in dan melakukan transaksi
                sebanyak lot transaksi yang disyaratkan.</p>
            <p>16) TPFx berhak untuk membatalkan Cashback Reward jika terbukti adanya kecurangan dan berbagai
                teknik manipulasi trading.</p>
            <p>17) Keputusan TPFx bersifat mutlak dan tidak dapat diganggu gugat.</p>
        </div>
    </section>
    @endif
    @if (isset($reward_cashback_tnc) && $source != 353627)
    <section>
        <div class="container py-5">
            <h2 id="tnc_cashback20reward" class="text-center fw-bold">TPFx Ramadan Promo</h2>
            </br>
            <p>Trijaya Pratama Futures atau TPFx merupakan perusahaan pialang berjangka yang sudah terdaftar dan
                teregulasi oleh BAPPEBTI dan JFX. Dapatkan Puluhan Gram Emas tanpa diundi dengan membuka akun real di
                TPFx dan melakukan transaksi sebanyak-banyaknya.
            </p>
            </br> </br>
            <h3>Periode Program</h3>
            <p>Periode Pendaftaran 8 April - 31 Mei 2022</p>
            <p>Periode Transaksi 1 Juni - 31 Juli 2022</p>
            </br>
            <h3>Syarat & Ketentuan Umum</h3>
            <p>1) Periode Promosi Program berlaku mulai 8 April hingga 31 Mei 2022</p>
            <p>2) Deposit Requirement & Reward Emas sebagai berikut:</p>
            </br>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Deposit Min</th>
                        <th>Reward Emas (gram)</th>
                        <th>Duration (month)</th>
                        <th>Lot Requirement</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>500</span></td>
                        <td>1</span></td>
                        <td>1</span></td>
                        <td>15</span></td>
                    </tr>
                    <tr>
                        <td>1000</span></td>
                        <td>2</span></td>
                        <td>1</span></td>
                        <td>30</span></td>
                    </tr>
                    <tr>
                        <td>2000</span></td>
                        <td>3</span></td>
                        <td>1</span></td>
                        <td>45</span></td>
                    </tr>
                    <tr>
                        <td>3000</span></td>
                        <td>3.5</span></td>
                        <td>1</span></td>
                        <td>60</span></td>
                    </tr>
                    <tr>
                        <td>4000</span></td>
                        <td>4</span></td>
                        <td>1</span></td>
                        <td>75</span></td>
                    </tr>
                    <tr>
                        <td>5000</span></td>
                        <td>5</span></td>
                        <td>1</span></td>
                        <td>90</span></td>
                    </tr>
                    <tr>
                        <td>6000</span></td>
                        <td>6</span></td>
                        <td>1</span></td>
                        <td>105</span></td>
                    </tr>
                    <tr>
                        <td>7000</span></td>
                        <td>7</span></td>
                        <td>1</span></td>
                        <td>120</span></td>
                    </tr>
                    <tr>
                        <td>8000</span></td>
                        <td>8</span></td>
                        <td>2</span></td>
                        <td>135</span></td>
                    </tr>
                    <tr>
                        <td>9000</span></td>
                        <td>9</span></td>
                        <td>2</span></td>
                        <td>150</span></td>
                    </tr>
                    <tr>
                        <td>10000</span></td>
                        <td>10</span></td>
                        <td>2</span></td>
                        <td>175</span></td>
                    </tr>
                </tbody>
            </table></br>
            <p>3) Program berlaku untuk nasabah yang melakukan pembukaan akun real baru di TPFx, mendaftar dan
                melakukan verifikasi untuk mengikuti program ini.
            </p>
            <p>4) Peserta pada program Reward Emas harus berusia 21 tahun atau lebih.
            </p>
            <p>5) Hanya berlaku untuk Forex, Metal, Energy dan Indices.</p>
            <p>6) Program ini berlaku untuk semua Account Type.
            </p>
            <p>7) Kriteria pemenang Reward Emas adalah berdasarkan nilai deposit awal dan total jumlah lot transaksi
                selama periode berlangsung.</p>
            <p>8) Setiap Nasabah hanya berhak mengklaim satu reward sesuai dengan jumlah lot transaksi yang dicapai.</p>
            <p>9) Jika nasabah tidak mencapai lot transaksi berdasarkan deposit awal, maka nasabah tetap bisa
                mengklaim reward sesuai dengan pencapaian lot transaksi.</p>
            <p>10) Peserta harus melakukan transaksi mandiri dan tidak dapat diwakilkan melalui sebuah kelompok,
                copy trade, robot trading, trade balance, arbitrase dan sebagainya.</p>
            <p>11) Peserta wajib melakukan klaim reward melalui form yang sudah ditentukan.</p>
            <p>12) Batas akhir klaim reward tanggal 5 Agustus 2022 (Semua klaim setelah tanggal tersebut tidak
                berlaku).</p>
            <p>13) Hadiah emas akan dikirimkan ke pemenang yang sudah didaftarkan di TPFx.
            </p>
            <p>14) Pajak hadiah ditanggung oleh pemenang</p>
            <p>15) Hadiah emas hanya akan diberikan jika peserta memenuhi syarat minimum margin in dan melakukan
                transaksi sebanyak lot transaksi yang disyaratkan.</p>
            <p>16) TPFx berhak untuk membatalkan Hadiah Emas jika terbukti adanya kecurangan dan berbagai teknik
                manipulasi trading.</p>
            <p>17) Keputusan TPFx bersifat mutlak dan tidak dapat diganggu gugat.</p>
        </div>
    </section>
    @endif

    <!--====== Start Programs Area ======-->
    <section class="programs-area">
        <div class="container">
            <div class="common-heading-2 text-center m-b-40">
                <h2 class="title" style="text-align: center;">{{ $section_2_heading }}</h2>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="program-item wow fadeInUp" data-wow-delay="0.2s">
                        <div class="image">
                            <img src="{{ asset('public/assets/img/' . $section_2_image_1) }}"
                                alt="{{ $section_2_title_1 }}">
                        </div>
                        <div class="content">
                            <h4>{{ $section_2_title_1 }}</h4>
                            <p>{{ $section_2_description_1 }}</p>
                        </div>
                        <div class="hover-bg" style="background-image: url(assets/img/tpfx/1.jpg);"></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="program-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="image">
                            <img src="{{ asset('public/assets/img/' . $section_2_image_2) }}"
                                alt="{{ $section_2_title_2 }}">
                        </div>
                        <div class="content">
                            <h4>{{ $section_2_title_2 }}</h4>
                            <p>{{ $section_2_description_2 }}</p>
                        </div>
                        <div class="hover-bg" style="background-image: url(assets/img/programs/program-hover.jpg);">
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="program-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="image">
                            <img src="{{ asset('public/assets/img/' . $section_2_image_3) }}"
                                alt="{{ $section_2_title_3 }}">
                        </div>
                        <div class="content">
                            <h4>{{ $section_2_title_3 }}</h4>
                            <p>{{ $section_2_description_3 }}</p>
                        </div>
                        <div class="hover-bg" style="background-image: url(assets/img/programs/program-hover.jpg);">
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="program-item wow fadeInUp" data-wow-delay="0.2s">
                        <div class="image">
                            <img src="{{ asset('public/assets/img/' . $section_2_image_4) }}"
                                alt="{{ $section_2_title_4 }}">
                        </div>
                        <div class="content">
                            <h4>{{ $section_2_title_4 }}</h4>
                            <p>{{ $section_2_description_4 }}</p>
                        </div>
                        <div class="hover-bg" style="background-image: url(assets/img/programs/program-hover.jpg);">
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="program-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="image">
                            <img src="{{ asset('public/assets/img/' . $section_2_image_5) }}"
                                alt="{{ $section_2_title_5 }}">
                        </div>
                        <div class="content">
                            <h4>{{ $section_2_title_5 }}</h4>
                            <p>{{ $section_2_description_5 }}</p>
                        </div>
                        <div class="hover-bg" style="background-image: url(assets/img/programs/program-hover.jpg);">
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="program-item wow fadeInUp" data-wow-delay="0.6s">
                        <div class="image">
                            <img src="{{ asset('public/assets/img/' . $section_2_image_6) }}"
                                alt="{{ $section_2_title_6 }}">
                        </div>
                        <div class="content">
                            <h4>{{ $section_2_title_6 }}</h4>
                            <p>{{ $section_2_description_6 }}</p>
                        </div>
                        <div class="hover-bg" style="background-image: url(assets/img/programs/program-hover.jpg);">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--====== End Programs Area ======-->

    <!--====== Start Payment System Section ======-->
    <section class="payment-section p-t-50 p-b-50 p-b-md-110">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-6">
                    <div class="preview-galley-v9">
                        <img src="{{ asset('public/assets/img/gold-section-3-compress-1.jpg') }}"
                            alt="{{ $section_3_heading }}" class="image-one">

                        <div class="icons">
                            <img src="assets/img/particle/wallet-icon.png" alt="icon"
                                class="icon-one animate-float-bob-y">
                            <img src="assets/img/particle/wallet-icon-2.png" alt="icon"
                                class="icon-two animate-zoominout">
                            <img src="assets/img/particle/wallet-icon-3.png" alt="icon"
                                class="icon-three animate-rotate-me">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-8 col-sm-10">
                    <div class="payment-content p-l-70 p-l-md-0 m-t-md-100">
                        <div class="common-heading m-b-50">
                            <h2 class="title" style="text-align: center;">{{ $section_3_heading }}</h2>
                        </div>
                        <p class="m-b-25">{{ $section_3_subheading }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--====== End Payment System Section ======-->

    <!--====== Start Benefit Section ======-->
    <section class="benefit-section">
        <div class="e-wallet-boxed-container">
            <div class="container">
                <div class="row align-items-center justify-content-lg-between justify-content-center">
                    <div class="col-lg-6 col-md-8">
                        <div class="benefit-content">
                            <div class="common-heading m-b-45" style="text-align: center;">
                                <h2 class="title">{{ $section_4_heading }}</h2>
                            </div>
                            <ul class="check-list-3 wow fadeInUp" data-wow-delay="0.2s">
                                <li>
                                    <h4 class="title">{{ $section_4_subheading_1 }}</h4>
                                    <p>{{ $section_4_description_1 }}</p>
                                </li>
                                <li>
                                    <h4 class="title">{{ $section_4_subheading_2 }}</h4>
                                    <p>{{ $section_4_description_2 }}</p>
                                </li>
                                <li>
                                    <h4 class="title">{{ $section_4_subheading_3 }}</h4>
                                    <p>{{ $section_4_description_3 }}</p>
                                </li>
                            </ul>
                            <a href="#book" class="template-btn bordered-body-4 m-t-40">
                                DAFTAR SEKARANG
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-8">
                        <div class="benefit-preview-images">
                            <div class="image-two animate-float-bob-y">
                                <img src="{{ asset('public/assets/img/gold-section-4-compress-2.jpg') }}"
                                    alt="{{ $section_4_heading }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--====== End Benefit Section ======-->


    <!--====== Start Scroll To Top ======-->
    <a href="#" class="back-to-top" id="scroll-top">
        <i class="far fa-angle-up"></i>
    </a>
    <!--====== End Scroll To Top ======-->

    <!--====== Start Footer ======-->
    <footer class="template-footer footer-bordered">
        <div class="container">
            <p style="text-align: center;font-size: 10px;padding-left: 50px;padding-right: 50px;color: white;">Trading
                derivatif yang mengandung sistem margin membawa keuntungan tinggi terhadap dana, tetapi juga dapat
                memberikan kerugian atas seluruh margin yang diperdagangkan. Pastikan anda benar-benar memahami resiko
                Trading derivatif dan mintalah nasihat consultant jika diperlukan. PT Trijaya Pratama Futures tidak
                bertanggung jawab atas segala bentuk kerugian.</p>
        </div>
        <div class="footer-copyright">
            <div class="container">
                <div class="align-items-center justify-content-between">
                    <div class="col-sm-auto col-12">
                        <p class="copyright-text text-center pt-4 pt-sm-0" style="font-size: 14px;">
                            Copyright © 2022 PT Trijaya Pratama Futures</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!--====== End Footer ======-->

    <!--====== Jquery ======-->
    <script src="{{ asset('public/assets/landio/js/jquery-3.6.0.min.js') }}"></script>
    <!--====== Bootstrap ======-->
    <script src="{{ asset('public/assets/landio/js/bootstrap.min.js') }}"></script>
    <!--====== Magnific ======-->
    <script src="{{ asset('public/assets/landio/js/jquery.magnific-popup.min.js') }}"></script>
    <!--====== Isotope Js ======-->
    <script src="{{ asset('public/assets/landio/js/isotope.pkgd.min.js') }}"></script>
    <!--====== Jquery UI Js ======-->
    <script src="{{ asset('public/assets/landio/js/jquery-ui.min.js') }}"></script>
    <!--====== Inview ======-->
    <script src="{{ asset('public/assets/landio/js/jquery.inview.min.js') }}"></script>
    <!--====== Nice Select ======-->
    <script src="{{ asset('public/assets/landio/js/jquery.nice-select.min.js') }}"></script>
    <!--====== Wow ======-->
    <script src="{{ asset('public/assets/landio/js/wow.min.js') }}"></script>
    <!--====== Main JS ======-->
    <script src="{{ asset('public/assets/landio/js/main.js') }}"></script>
</body>

</html>
