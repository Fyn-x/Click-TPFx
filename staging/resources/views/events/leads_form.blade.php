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
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>TPFx - {{ $title }}</title>
    <!--====== Favicon Icon ======-->
    <link rel="icon" type="image/png" href="{{ asset('public/assets/img/favicon-32x32.png') }}">
    <!-- all css -->
    <link rel="stylesheet" href="{{ asset('public/assets/zeid/css/all.min.css') }}" media="all" defer />
    <link rel="stylesheet" href="{{ asset('public/assets/zeid/css/style.css') }}" media="all" defer />
    <link rel="stylesheet" href="{{ asset('public/assets/zeid/fonts/fontawesome/css/all.min.css') }}" media="all"
        defer />

    <meta name="facebook-domain-verification" content="diuhpyqhjrcd32phqlrct3nudrucem" />
    <!-- Google Tag Manager -->
    <script>
        (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-WWQKGDV');
    </script>
    <!-- End Google Tag Manager -->
    <!-- Twitter universal website tag code -->
    <script async>
        ! function (e, t, n, s, u, a) {
            e.twq || (s = e.twq = function () {
                    s.exe ? s.exe.apply(s, arguments) : s.queue.push(arguments);
                }, s.version = '1.1', s.queue = [], u = t.createElement(n), u.media = "all" = !0, u.src =
                '//static.ads-twitter.com/uwt.js', a = t.getElementsByTagName(n)[0], a.parentNode.insertBefore(u, a)
            )
        }(window, document, 'script');
        twq('init', 'o82y0');
        twq('track', 'PageView');
    </script>
    <!-- End Twitter universal website tag code -->
    <!-- Mgid Sensor -->
    <script type="text/javascript" async>
        (function () {
            var d = document,
                w = window;
            w.MgSensorData = w.MgSensorData || [];
            w.MgSensorData.push({
                cid: 712553,
                lng: "us",
                project: "a.mgid.com"
            });
            var l = "a.mgid.com";
            var n = d.getElementsByTagName("script")[0];
            var s = d.createElement("script");
            s.type = "text/javascript";
            s.media = "all" = true;
            var dt = !Date.now ? new Date().valueOf() : Date.now();
            s.src = "https://" + l + "/mgsensor.js?d=" + dt;
            n.parentNode.insertBefore(s, n);
        })(); >
    </script>
    <!-- /Mgid Sensor -->
    <script data-obct type="text/javascript" async>
        ! function (_window, _document) {
            var OB_ADV_ID = '0069545528e42078ca6ee9d3c5ce1cb5e9';
            if (_window.obApi) {
                var toArray = function (object) {
                    return Object.prototype.toString.call(object) === '[object Array]' ? object : [object];
                };
                _window.obApi.marketerId = toArray(_window.obApi.marketerId).concat(toArray(OB_ADV_ID));
                return;
            }
            var api = _window.obApi = function () {
                api.dispatch ? api.dispatch.apply(api, arguments) : api.queue.push(arguments);
            };
            api.version = '1.1';
            api.loaded = true;
            api.marketerId = OB_ADV_ID;
            api.queue = [];
            var tag = _document.createElement('script');
            tag.media = "all" = true;
            tag.src = '//amplify.outbrain.com/cp/obtp.js';
            tag.type = 'text/javascript';
            var script = _document.getElementsByTagName('script')[0];
            script.parentNode.insertBefore(tag, script);
        }(window, document);
        obApi('track', 'PAGE_VIEW');
    </script>
</head>

<body class="e-wallet-landing">
    <!-- Google Tag Manager (noscript) -->
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
                                <img src="{{ asset('public/assets/img/tpfx/logo15.png') }}" alt="logo tpfx">
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
                    <img class="w-100" src="{{ asset('public/assets/img/' . $banner_image_desktop) }}" />
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
    <section class="booking-section p-t-50 p-b-50" id="registration_section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-lg-6 col-md-10">
                            <div class="booking-text pr-xl-3 p-b-md-50 text-center">
                                <div class="common-heading-2 m-b-30">
                                    <h1 style="text-align: left;">{{ $section_1_heading }}</h1>
                                </div>
                                <h4 class="text-justify">{!! nl2br($section_1_subheading) !!}</h4>
                                @isset($reward_cashback_tnc)
                                <a href="#tnc_cashback20reward">
                                    <p class="mt-3 text-white"><strong>*syarat dan ketentuan berlaku</strong></p>
                                </a>
                                @endisset
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="booking-form wow fadeInUp ml-xl-3">
                                <h4 class="form-title text-center">{{ $form_heading }}</h4>
                                <p>{{ $form_subheading }}</p>
                                <form id="leadsForm" role="form">
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
                                            pattern="(08|628)\d{8,14}" required>
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

    @if ($utm_campaign == 'cashback20rewards' && $source == 353627)
    <section>
        <div class="container py-5 text-white">
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
            <table class="table table-striped text-white">
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
    @if ($utm_campaign == 'cashback20rewards' && $source != 353627)
    <section>
        <div class="container py-5 text-white">
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
            <table class="table table-striped text-white">
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
    @if ($utm_campaign == 'smartphonegratis')
    <section>
        <div class="container py-5 text-white">
            <h2 id="tnc_cashback20reward" class="text-justify fw-bold">Gebyar Smartphone Gratis TPFx</h2>
            </br>
            <p>Trijaya Pratama Futures atau TPFx merupakan perusahaan pialang berjangka yang sudah terdaftar dan
                teregulasi oleh BAPPEBTI dan JFX. Dapatkan Puluhan Smartphone tanpa diundi dengan membuka akun real di
                TPFx dan melakukan transaksi sebanyak-banyaknya.
            </p>
            </br> </br>
            <h3>Periode Program</h3>
            <p>Periode Pendaftaran : 20 Juni 2022 - 19 Agustus 2022</p>
            <p>Periode Transaksi : 20 Juni 2022 - 19 Oktober 2022</p>
            </br>
            <h3>Syarat & Ketentuan Umum</h3>
            <p>1) Periode Promosi Program berlaku mulai 20 Juni 2022 - 19 Agustus 2022</p>
            <p>2) Deposit Requirement & Reward Smartphone Gratis sebagai berikut:</p>
            </br>
            <table class="table table-striped text-white">
                <thead>
                    <tr>
                        <th>Deposit Min</th>
                        <th>Reward Smartphone</th>
                        <th>Duration (Month)</th>
                        <th>Avg. Lot</th>
                        <th>Jenis Reward</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="27">$500</td>
                        <td>Samsung galaxy A20s</td>
                        <td>1</td>
                        <td rowspan="4">40 Lot</td>
                        <td rowspan="4">Mini 500</td>
                    </tr>
                    <tr>
                        <td>oppo A16</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Vivo Y15s</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Redmi note 9</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Realme 8i</td>
                        <td>1</td>
                        <td rowspan="4">55 Lot</td>
                        <td rowspan="4">Small 500</td>
                    </tr>
                    <tr>
                        <td>Samsung galaxy A13</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Vivo Y33T</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Oppo A16</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Samsung galaxy A32</td>
                        <td>1</td>
                        <td rowspan="4">80 Lot</td>
                        <td rowspan="4">Medium 500</td>
                    </tr>
                    <tr>
                        <td>Vivo V21</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Realme Narzo 50</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>oppo A96</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>samsung galaxy A33</td>
                        <td>1</td>
                        <td rowspan="4">110 Lot</td>
                        <td rowspan="4">Super Medium 500</td>
                    </tr>
                    <tr>
                        <td>xiaomi poco X3 pro</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Realme GT master edition</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Oppo Reno 6</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Vivo V23</td>
                        <td>1</td>
                        <td rowspan="3">135 Lot</td>
                        <td rowspan="3">Classy 500</td>
                    </tr>
                    <tr>
                        <td>samsung galaxy S9</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>oppo reno 5 5G</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>xiaomi 11T pro</td>
                        <td>1</td>
                        <td rowspan="4">175 Lot</td>
                        <td rowspan="4">Deluxe 500</td>
                    </tr>
                    <tr>
                        <td>Iphone SE 3rd</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>samsung galaxy A73</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>vivo Z1</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Iphone 11 128GB</td>
                        <td>2</td>
                        <td rowspan="4">220 Lot</td>
                        <td rowspan="4">Luxury 500</td>
                    </tr>
                    <tr>
                        <td>oppo reno 6 pro 5g</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>SAMSUNG GALAXY S10 5G</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Xiaomi 12</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td rowspan="10">$2,500</td>
                        <td>samsung galaxy S22/256GB</td>
                        <td>2</td>
                        <td rowspan="3">285 Lot</td>
                        <td rowspan="3">Medium 2500</td>
                    </tr>
                    <tr>
                        <td>iphone 12 128gb</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>oppo find X3 pro</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>xiaomi mi 11 ultra</td>
                        <td>2</td>
                        <td rowspan="3">325 Lot</td>
                        <td rowspan="3">Super Medium 2500</td>
                    </tr>
                    <tr>
                        <td>samsung galaxy Z flip 5G 256gb</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Samsung galaxy Z fold 5G 256GB</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Iphone 13 128GB</td>
                        <td>2</td>
                        <td rowspan="2">350 Lot</td>
                        <td rowspan="2">Classy 2500</td>
                    </tr>
                    <tr>
                        <td>samsung Galaxy S22+ /256GB</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Samsung Galaxy S22 Ultra 5G / 256GB</td>
                        <td>2</td>
                        <td rowspan="2">440 Lot</td>
                        <td rowspan="2">Deluxe 2500</td>
                    </tr>
                    <tr>
                        <td>iphone 13 pro max 128gb</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td rowspan="3">$5,000</td>
                        <td>samsung Z fold 5G 512GB</td>
                        <td>2</td>
                        <td rowspan="3">520 Lot</td>
                        <td rowspan="3">Luxury 5000</td>
                    </tr>
                    <tr>
                        <td>iphone 13 pro max 256gb</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Vivo x fold 5G 256GB</td>
                        <td>2</td>
                    </tr>
                </tbody>
            </table></br>
            <p>3) Program berlaku untuk nasabah yang melakukan pembukaan akun real baru di TPFx, mendaftar dan melakukan
                verifikasi untuk mengikuti program ini</p>
            <p>4) Peserta pada program Gebyar Smartphone Gratis harus berusia 21 tahun atau lebih</p>
            <p>5) Hanya berlaku untuk Forex, Metal, Energy dan Indices</p>
            <p>6) Program ini berlaku untuk semua Account Type</p>
            <p>7) Kriteria pemenang Gebyar Smartphone Gratis adalah berdasarkan nilai deposit awal dan total jumlah lot
                transaksi selama periode berlangsung</p>
            <p>8) Setiap Nasabah hanya berhak mengklaim satu reward sesuai dengan jumlah lot transaksi yang dicapai</p>
            <p>9) Jika nasabah tidak mencapai lot transaksi berdasarkan deposit awal, maka nasabah tetap bisa mengklaim
                reward sesuai dengan pencapaian lot transaksi</p>
            <p>10) Peserta harus melakukan transaksi mandiri dan tidak dapat diwakilkan melalui sebuah kelompok, copy
                trade, robot trading, trade balance, arbitrase dan sebagainya</p>
            <p>11) Peserta wajib melakukan klaim reward melalui form yang sudah ditentukan.
            </p>
            <p>12) Batas akhir klaim reward tanggal 19 Oktober 2022 (Semua klaim setelah tanggal tersebut tidak berlaku)
            </p>
            <p>13) Hadiah smartphone akan dikirimkan ke pemenang yang sudah didaftarkan di TPFx</p>
            <p>14) Pajak hadiah ditanggung oleh pemenang
            </p>
            <p>15) Hadiah smartphone hanya akan diberikan jika peserta memenuhi syarat minimum margin in dan melakukan
                transaksi sebanyak lot transaksi yang disyaratkan</p>
            <p>16) TPFx berhak untuk membatalkan Hadiah Gebyar Smartphone Gratis jika terbukti adanya kecurangan dan
                berbagai teknik manipulasi trading</p>
            <p>17) Keputusan TPFx bersifat mutlak dan tidak dapat diganggu gugat</p>
            <p>18) Jika barang tidak tersedia maka akan diganti dengan barang lain dengan nilai yang sama</p>
        </div>
    </section>
    @endif
    @if ($utm_campaign == 'tpfxgadget')
    <section>
        <div class="container py-5 text-white">
            <h2 id="tnc_cashback20reward" class="text-center fw-bold">TPFx Bagi-Bagi Gadget</h2>
            </br>
            <p>TPFx (Trijaya Pratama Futures) merupakan perusahaan pialang berjangka yang sudah terdaftar dan teregulasi
                oleh BAPPEBTI dan JFX.
                Dapatkan Puluhan Smartphone tanpa diundi dengan membuka akun real di TPFx dan melakukan transaksi
                sebanyak-banyaknya
            </p>
            </br> </br>
            <h3>Periode Program</h3>
            <p>Periode Pendaftaran : 20 Juni 2022 - 19 Agustus 2022</p>
            <p>Periode Transaksi : 20 Juni 2022 - 19 Oktober 2022</p>
            </br>
            <h3>Syarat & Ketentuan Umum</h3>
            <p>1) Periode Promosi Program berlaku mulai 20 Juni 2022 - 19 Agustus 2022</p>
            <p>2) Deposit Requirement & Reward Bagi-Bagi Gadget sebagai berikut:</p>
            </br>
            <table class="table table-striped text-white">
                <thead>
                    <tr>
                        <th>Deposit Min</th>
                        <th>Reward Smartphone</th>
                        <th>Duration (Month)</th>
                        <th>Avg. Lot</th>
                        <th>Jenis Reward</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="27">$500</td>
                        <td>Samsung galaxy A20s</td>
                        <td>1</td>
                        <td rowspan="4">40 Lot</td>
                        <td rowspan="4">Mini 500</td>
                    </tr>
                    <tr>
                        <td>oppo A16</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Vivo Y15s</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Redmi note 9</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Realme 8i</td>
                        <td>1</td>
                        <td rowspan="4">55 Lot</td>
                        <td rowspan="4">Small 500</td>
                    </tr>
                    <tr>
                        <td>Samsung galaxy A13</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Vivo Y33T</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Oppo A16</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Samsung galaxy A32</td>
                        <td>1</td>
                        <td rowspan="4">80 Lot</td>
                        <td rowspan="4">Medium 500</td>
                    </tr>
                    <tr>
                        <td>Vivo V21</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Realme Narzo 50</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>oppo A96</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>samsung galaxy A33</td>
                        <td>1</td>
                        <td rowspan="4">110 Lot</td>
                        <td rowspan="4">Super Medium 500</td>
                    </tr>
                    <tr>
                        <td>xiaomi poco X3 pro</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Realme GT master edition</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Oppo Reno 6</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Vivo V23</td>
                        <td>1</td>
                        <td rowspan="3">135 Lot</td>
                        <td rowspan="3">Classy 500</td>
                    </tr>
                    <tr>
                        <td>samsung galaxy S9</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>oppo reno 5 5G</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>xiaomi 11T pro</td>
                        <td>1</td>
                        <td rowspan="4">175 Lot</td>
                        <td rowspan="4">Deluxe 500</td>
                    </tr>
                    <tr>
                        <td>Iphone SE 3rd</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>samsung galaxy A73</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>vivo Z1</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Iphone 11 128GB</td>
                        <td>2</td>
                        <td rowspan="4">220 Lot</td>
                        <td rowspan="4">Luxury 500</td>
                    </tr>
                    <tr>
                        <td>oppo reno 6 pro 5g</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>SAMSUNG GALAXY S10 5G</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Xiaomi 12</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td rowspan="10">$2,500</td>
                        <td>samsung galaxy S22/256GB</td>
                        <td>2</td>
                        <td rowspan="3">285 Lot</td>
                        <td rowspan="3">Medium 2500</td>
                    </tr>
                    <tr>
                        <td>iphone 12 128gb</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>oppo find X3 pro</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>xiaomi mi 11 ultra</td>
                        <td>2</td>
                        <td rowspan="3">325 Lot</td>
                        <td rowspan="3">Super Medium 2500</td>
                    </tr>
                    <tr>
                        <td>samsung galaxy Z flip 5G 256gb</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Samsung galaxy Z fold 5G 256GB</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Iphone 13 128GB</td>
                        <td>2</td>
                        <td rowspan="2">350 Lot</td>
                        <td rowspan="2">Classy 2500</td>
                    </tr>
                    <tr>
                        <td>samsung Galaxy S22+ /256GB</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Samsung Galaxy S22 Ultra 5G / 256GB</td>
                        <td>2</td>
                        <td rowspan="2">440 Lot</td>
                        <td rowspan="2">Deluxe 2500</td>
                    </tr>
                    <tr>
                        <td>iphone 13 pro max 128gb</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td rowspan="3">$5,000</td>
                        <td>samsung Z fold 5G 512GB</td>
                        <td>2</td>
                        <td rowspan="3">520 Lot</td>
                        <td rowspan="3">Luxury 5000</td>
                    </tr>
                    <tr>
                        <td>iphone 13 pro max 256gb</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Vivo x fold 5G 256GB</td>
                        <td>2</td>
                    </tr>
                </tbody>
            </table></br>
            <p>3) Program berlaku untuk nasabah yang melakukan pembukaan akun real baru di TPFx, mendaftar dan melakukan
                verifikasi untuk mengikuti program ini</p>
            <p>4) Peserta pada program Gebyar Smartphone Gratis harus berusia 21 tahun atau lebih </p>
            <p>5) Hanya berlaku untuk Forex, Metal, Energy dan Indices.
            </p>
            <p>6) Program ini berlaku untuk semua Account Type.
            </p>
            <p>7) Kriteria pemenang Gebyar Smartphone Gratis adalah berdasarkan nilai deposit awal dan total jumlah lot
                transaksi selama periode berlangsung</p>
            <p>8) Setiap Nasabah hanya berhak mengklaim satu reward sesuai dengan jumlah lot transaksi yang dicapai </p>
            <p>9) Jika nasabah tidak mencapai lot transaksi berdasarkan deposit awal, maka nasabah tetap bisa mengklaim
                reward sesuai dengan pencapaian lot transaksi</p>
            <p>10) Peserta harus melakukan transaksi mandiri dan tidak dapat diwakilkan melalui sebuah kelompok, copy
                trade, robot trading, trade balance, arbitrase dan sebagainya</p>
            <p>11) Peserta wajib melakukan klaim reward melalui form yang sudah ditentukan.
            </p>
            <p>12) Batas akhir klaim reward tanggal 19 Oktober 2022 (Semua klaim setelah tanggal tersebut tidak
                berlaku).
            </p>
            <p>13) Hadiah gadget akan dikirimkan ke pemenang yang sudah didaftarkan di TPFx.
            </p>
            <p>14) Pajak hadiah ditanggung oleh pemenang.
            </p>
            <p>15) Hadiah gadget hanya akan diberikan jika peserta memenuhi syarat minimum margin in dan melakukan
                transaksi sebanyak lot transaksi yang disyaratkan.
            </p>
            <p>16) TPFx berhak untuk membatalkan Hadiah Gadget jika terbukti adanya kecurangan dan berbagai teknik
                manipulasi trading.
            </p>
            <p>17) Keputusan TPFx bersifat mutlak dan tidak dapat diganggu gugat.
            </p>
            <p>18) Jika barang tidak tersedia maka akan diganti dengan barang lain dengan nilai yang sama</p>
        </div>
    </section>
    @endif
    @if ($utm_campaign == 'merdekatradefest')
    <section>
        <div class="container py-5 text-white">
            <h2 id="tnc_cashback20reward" class="text-center fw-bold">TPFx Merdeka Tradefest</h2>
            </br>
            <p>TPFx (Trijaya Pratama Futures) merupakan perusahaan pialang berjangka yang sudah terdaftar dan teregulasi
                oleh BAPPEBTI dan JFX.
                Dapatkan Puluhan Smartphone tanpa diundi dengan membuka akun real di TPFx dan melakukan transaksi
                sebanyak-banyaknya.

            </p>
            </br> </br>
            <h3>Periode Program</h3>
            <p>Periode Pendaftaran : 01 Agustus 2023 - 30 September 2023</p>
            <p>Periode Transaksi : 01 Agustus 2023 - 30 November 2023</p>
            </br>
            <h3>Syarat & Ketentuan Umum</h3>
            <p>1) Periode Promosi Program berlaku mulai 01 Agustus 2023 - 30 September 2023.</p></br>
            <p>2) Deposit Requirement & Reward Merdeka Tradefest sebagai berikut:</p>
            </br>
            <table border="1" width="100%" color=" #000">
                <tbody color=" #000">
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>No</strong></p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>Reward HP</strong></p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>Min Deposit</strong></p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>Jumlah Lot</strong></p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>Duration (Month)</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>1</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Samsung Galaxy A04</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$500</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">60</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">1</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>2</strong></p>
                        </td>
                        <td>
                            <p class="text-white">OPPO A55 4GB/64GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$500</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">100</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">1</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>3</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Vivo iQOO 8GB/256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$500</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">110</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">1</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>4</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Redmi Note 12 Pro 5G (8GB+5GB/256GB)</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$500</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">140</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">1</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>5</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Vivo V25 5GB (8/256)</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$500</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">180</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">1</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>6</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Xiaomi 12T 5G (8/256GB)</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$500</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">190</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">1</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>7</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Oppo Reno8 5G 8GB/256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$1,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">230</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>8</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Samsung Galaxy S21 FE 5G 8/128GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$1,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">260</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>9</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Vivo iQOO 11 (16/256 GB)</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$1,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">320</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>10</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Iphone 12 128GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$1,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">380</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>11</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Samsung Galaxy Z Flip4 5G 8/256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$1,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">430</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>12</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Samsung Galaxy S23+ 5G 8GB/256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$1,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">460</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>13</strong></p>
                        </td>
                        <td>
                            <p class="text-white">iPhone 13 256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$2,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">520</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>14</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Samsung Galaxy S23 Ultra 5G 12GB/256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$2,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">600</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>15</strong></p>
                        </td>
                        <td>
                            <p class="text-white">iPhone 14 Pro Max 256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$2,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">720</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                </tbody>
            </table></br>
            <p>3) Program berlaku untuk nasabah yang melakukan pembukaan akun real baru di TPFx, mendaftar dan melakukan
                verifikasi untuk mengikuti program ini.</p></br>
            <p>4) Peserta pada program TPFx Merdeka Tradefest harus berusia 21 tahun atau lebih.</p></br>
            <p>5) Hanya berlaku untuk Forex, Metal, Energy dan Indices.</p></br>
            <p>6) Program ini berlaku untuk semua Account Type.</p></br>
            <p>7) Kriteria pemenang Merdeka Tradefest adalah berdasarkan nilai deposit awal dan total jumlah lot
                transaksi selama periode berlangsung.</p></br>
            <p>8) Setiap Nasabah hanya berhak mengklaim satu reward sesuai dengan jumlah lot transaksi yang dicapai.</p>
            </br>
            <p>9) Apabila transaksi tidak mencapai lot berdasarkan ketentuan deposit awal, nasabah tidak dapat mengklaim
                reward sesuai dengan pencapaian lot transaksi.</p></br>
            <p>10) Peserta harus melakukan transaksi mandiri dan tidak dapat diwakilkan melalui sebuah kelompok, copy
                trade, robot trading, trade balance, arbitrase dan sebagainya.</p></br>
            <p>11) Peserta wajib melakukan klaim reward melalui form yang sudah ditentukan.
            </p></br>
            <p>12) Batas akhir klaim reward tanggal 30 November 2023 (Semua klaim setelah tanggal tersebut tidak
                berlaku).
            </p></br>
            <p>13) Hadiah smartphone akan dikirimkan ke pemenang yang sudah didaftarkan di TPFx.</p></br>
            <p>14) Pajak hadiah ditanggung oleh pemenang.
            </p></br>
            <p>15) Hadiah Gadget hanya akan diberikan jika peserta memenuhi syarat minimum deposit awal dan melakukan
                transaksi sebanyak lot transaksi yang disyaratkan.</p></br>
            <p>16) Apabila transaksi tidak memenuhi syarat ketentuan pencapaian lot maka reward yang dapat diklaim
                adalah Samsung A04 dengan syarat minimal transaksi diatas 60 lot untuk durasi 1 bulan dan 120 lot untuk
                durasi 2 bulan.</p></br>
            <p>17) TPFx berhak untuk membatalkan reward jika terbukti adanya kecurangan dan berbagai teknik manipulasi
                trading.</p></br>
            <p>18) Keputusan TPFx bersifat mutlak dan tidak dapat diganggu gugat.</p></br>
            <p>19) Jika barang tidak tersedia maka akan diganti dengan barang lain dengan nilai yang sama.</p></br>
        </div>
    </section>
    @endif
    @if ($utm_campaign == 'merdeka-tradefest')
    <section>
        <div class="container py-5 text-white">
            <h2 id="tnc_cashback20reward" class="text-center fw-bold">TPFx Merdeka Tradefest</h2>
            </br>
            <p>TPFx (Trijaya Pratama Futures) merupakan perusahaan pialang berjangka yang sudah terdaftar dan teregulasi
                oleh BAPPEBTI dan JFX.
                Dapatkan Puluhan Smartphone tanpa diundi dengan membuka akun real di TPFx dan melakukan transaksi
                sebanyak-banyaknya.

            </p>
            </br> </br>
            <h3>Periode Program</h3>
            <p>Periode Pendaftaran : 01 Agustus 2023 - 30 September 2023</p>
            <p>Periode Transaksi : 01 Agustus 2023 - 30 November 2023</p>
            </br>
            <h3>Syarat & Ketentuan Umum</h3>
            <p>1) Periode Promosi Program berlaku mulai 01 Agustus 2023 - 30 September 2023.</p></br>
            <p>2) Deposit Requirement & Reward Merdeka Tradefest sebagai berikut:</p>
            </br>
            <table border="1" width="100%" color=" #000">
                <tbody color=" #000">
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>No</strong></p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>Reward HP</strong></p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>Min Deposit</strong></p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>Jumlah Lot</strong></p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>Duration (Month)</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>1</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Samsung Galaxy A04</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$500</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">60</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">1</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>2</strong></p>
                        </td>
                        <td>
                            <p class="text-white">OPPO A55 4GB/64GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$500</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">100</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">1</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>3</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Vivo iQOO 8GB/256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$500</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">110</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">1</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>4</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Redmi Note 12 Pro 5G (8GB+5GB/256GB)</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$500</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">140</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">1</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>5</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Vivo V25 5GB (8/256)</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$500</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">180</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">1</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>6</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Xiaomi 12T 5G (8/256GB)</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$500</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">190</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">1</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>7</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Oppo Reno8 5G 8GB/256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$1,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">230</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>8</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Samsung Galaxy S21 FE 5G 8/128GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$1,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">260</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>9</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Vivo iQOO 11 (16/256 GB)</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$1,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">320</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>10</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Iphone 12 128GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$1,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">380</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>11</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Samsung Galaxy Z Flip4 5G 8/256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$1,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">430</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>12</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Samsung Galaxy S23+ 5G 8GB/256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$1,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">460</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>13</strong></p>
                        </td>
                        <td>
                            <p class="text-white">iPhone 13 256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$2,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">520</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>14</strong></p>
                        </td>
                        <td>
                            <p class="text-white">Samsung Galaxy S23 Ultra 5G 12GB/256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$2,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">600</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p class="text-white"><strong>15</strong></p>
                        </td>
                        <td>
                            <p class="text-white">iPhone 14 Pro Max 256GB</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">$2,000</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">720</p>
                        </td>
                        <td style="text-align: center;">
                            <p class="text-white">2</p>
                        </td>
                    </tr>
                </tbody>
            </table></br>
            <p>3) Program berlaku untuk nasabah yang melakukan pembukaan akun real baru di TPFx, mendaftar dan melakukan
                verifikasi untuk mengikuti program ini.</p></br>
            <p>4) Peserta pada program TPFx Merdeka Tradefest harus berusia 21 tahun atau lebih.</p></br>
            <p>5) Hanya berlaku untuk Forex, Metal, Energy dan Indices.</p></br>
            <p>6) Program ini berlaku untuk semua Account Type.</p></br>
            <p>7) Kriteria pemenang Merdeka Tradefest adalah berdasarkan nilai deposit awal dan total jumlah lot
                transaksi selama periode berlangsung.</p></br>
            <p>8) Setiap Nasabah hanya berhak mengklaim satu reward sesuai dengan jumlah lot transaksi yang dicapai.</p>
            </br>
            <p>9) Apabila transaksi tidak mencapai lot berdasarkan ketentuan deposit awal, nasabah tidak dapat mengklaim
                reward sesuai dengan pencapaian lot transaksi.</p></br>
            <p>10) Peserta harus melakukan transaksi mandiri dan tidak dapat diwakilkan melalui sebuah kelompok, copy
                trade, robot trading, trade balance, arbitrase dan sebagainya.</p></br>
            <p>11) Peserta wajib melakukan klaim reward melalui form yang sudah ditentukan.
            </p></br>
            <p>12) Batas akhir klaim reward tanggal 30 November 2023 (Semua klaim setelah tanggal tersebut tidak
                berlaku).
            </p></br>
            <p>13) Hadiah smartphone akan dikirimkan ke pemenang yang sudah didaftarkan di TPFx.</p></br>
            <p>14) Pajak hadiah ditanggung oleh pemenang.
            </p></br>
            <p>15) Hadiah Gadget hanya akan diberikan jika peserta memenuhi syarat minimum deposit awal dan melakukan
                transaksi sebanyak lot transaksi yang disyaratkan.</p></br>
            <p>16) Apabila transaksi tidak memenuhi syarat ketentuan pencapaian lot maka reward yang dapat diklaim
                adalah Samsung A04 dengan syarat minimal transaksi diatas 60 lot untuk durasi 1 bulan dan 120 lot untuk
                durasi 2 bulan.</p></br>
            <p>17) TPFx berhak untuk membatalkan reward jika terbukti adanya kecurangan dan berbagai teknik manipulasi
                trading.</p></br>
            <p>18) Keputusan TPFx bersifat mutlak dan tidak dapat diganggu gugat.</p></br>
            <p>19) Jika barang tidak tersedia maka akan diganti dengan barang lain dengan nilai yang sama.</p></br>
        </div>
    </section>
    @endif
    @if ($utm_campaign == 'bigdeal')
    <section>
        <div class="container py-5 text-white">
            <h2 id="tnc_cashback20reward" class="text-center fw-bold">Merdeka Big Deals</h2>
            <br>
            <p>TPFx (Trijaya Pratama Futures) merupakan perusahaan pialang berjangka yang
                sudah terdaftar dan teregulasi oleh BAPPEBTI dan JFX.<br>Dapatkan Mercedes C200, Mitsubishi New Xpander
                dan puluhan hadiah lainnya
                tanpa diundi dengan membuka akun real di TPFx dan melakukan transaksi
                sebanyak-banyaknya.</p>
            <br> <br>
            <h3>Periode Program</h3>
            <p>Periode Pendaftaran : 15 Agustus 2022 - 30 November 2022</p>
            <p>Periode Transaksi : hingga 31 Mei 2023</p>
            <br>
            <h3>Syarat &amp; Ketentuan Umum</h3>
            <p>1) Periode Merdeka Big Deals berlaku mulai 15 Agustus - 30 November 2022</p>
            <p>2) Deposit Requirement &amp; Reward Merdeka Big Deals sebagai berikut:</p>
            <br>
            <table class="table table-striped text-white">
                <thead>
                    <tr>
                        <th>Jenis Barang</th>
                        <th>Min Deposit</th>
                        <th>Lot</th>
                        <th>Trade<br>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Mercedes-Benz C 200 Avantgarde Line</td>
                        <td>10000</td>
                        <td>19.500</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td>Mitsubishi New Xpander Exceed CVT</td>
                        <td>5000</td>
                        <td>5.600</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td>Tour Europe 13D12N (2Pax)</td>
                        <td>1000</td>
                        <td>1.400</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>CBR 150R Victory Black Red ABS</td>
                        <td>1000</td>
                        <td>800</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>MacBook Pro M1 2021</td>
                        <td>1000</td>
                        <td>600</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>DJI Mavic Air 2S Fly more Combo</td>
                        <td>1000</td>
                        <td>410</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Tour Labuan Bajo 3D2N (2Pax)</td>
                        <td>1000</td>
                        <td>400</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>BeAT CBS ISS</td>
                        <td>1000</td>
                        <td>360</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Iphone 13 Pro</td>
                        <td>1000</td>
                        <td>350</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Samsung Galaxy Z Flip3 5G</td>
                        <td>500</td>
                        <td>310</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Samsung TV 65"UHD 4K AU7000</td>
                        <td>500</td>
                        <td>300</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>iPad Pro</td>
                        <td>500</td>
                        <td>270</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Sony RX100 IV</td>
                        <td>500</td>
                        <td>230</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Treadmil I Turin Motorized</td>
                        <td>500</td>
                        <td>180</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Oculus Metaverse Quest 2</td>
                        <td>500</td>
                        <td>130</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Galaxy Watch4 Classic LTE</td>
                        <td>500</td>
                        <td>120</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>Logam Mulia 5gr</td>
                        <td>500</td>
                        <td>100</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>AirPods Pro</td>
                        <td>500</td>
                        <td>90</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>JBL Charge 4 Portable Bluetooth Speaker</td>
                        <td>500</td>
                        <td>60</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Logam Mulia 2gr</td>
                        <td>500</td>
                        <td>40</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Voucher Map/Carrefour 1jt</td>
                        <td>500</td>
                        <td>30</td>
                        <td>1</td>
                    </tr>
                </tbody>
            </table><br>
            <p>3) Program berlaku untuk nasabah yang melakukan pembukaan akun real baru di TPFx, mendaftar dan melakukan
                verifikasi untuk mengikuti program ini</p>
            <p>4) Peserta pada program Gebyar Merdeka Big Deals harus berusia 21 tahun atau lebih</p>
            <p>5) Hanya berlaku untuk Forex, Metal, Energy dan Indices</p>
            <p>6) Program ini berlaku untuk semua Account Type</p>
            <p>7) Kriteria pemenang Merdeka Big Deals adalah berdasarkan nilai deposit awal dan total jumlah lot
                transaksi selama periode berlangsung</p>
            <p>8) Setiap Nasabah hanya berhak mengklaim satu reward sesuai dengan jumlah lot transaksi yang dicapai</p>
            <p>9) Jika nasabah tidak mencapai lot transaksi berdasarkan deposit awal, maka nasabah tetap bisa mengklaim
                reward sesuai dengan pencapaian lot transaksi</p>
            <p>10) Peserta harus melakukan transaksi mandiri dan tidak dapat diwakilkan melalui sebuah kelompok, copy
                trade, robot trading, trade balance, arbitrase dan sebagainya</p>
            <p>11) Peserta wajib melakukan klaim reward melalui form yang sudah ditentukan.
            </p>
            <p>12) Batas akhir klaim reward tanggal 31 Mei 2023 (Semua klaim setelah tanggal tersebut tidak berlaku)</p>
            <p>13) Hadiah Merdeka Big Deals akan dikirimkan ke pemenang yang sudah didaftarkan di TPFx</p>
            <p>14) Pajak hadiah ditanggung oleh pemenang
            </p>
            <p>15) Hadiah Merdeka Big Deals hanya akan diberikan jika peserta memenuhi syarat minimum margin in dan
                melakukan transaksi sebanyak lot transaksi yang disyaratkan</p>
            <p>16) TPFx berhak untuk membatalkan Merdeka Big Deals jika terbukti adanya kecurangan dan berbagai teknik
                manipulasi trading</p>
            <p>17) Keputusan TPFx bersifat mutlak dan tidak dapat diganggu gugat</p>
            <p>18) Jika barang tidak tersedia maka akan diganti dengan barang lain dengan nilai yang sama</p>
        </div>
    </section>
    @endif

    <!--====== Start Programs Area ======-->
    <section class="programs-area">
        <div class="container">
            <div class="common-heading-2 text-center m-b-20">
                <h2 class="title" style="text-align: center;">{{ $section_2_heading }}</h2>
                <h2 class="title" style="text-align: center;">{{ $section_2_subheading }}</h2>
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
                        <div style="background-image: url(assets/img/tpfx/1.jpg);"></div>
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
                        <div style="background-image: url(assets/img/programs/program-hover.jpg);">
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
                        <div style="background-image: url(assets/img/programs/program-hover.jpg);">
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
                        <div style="background-image: url(assets/img/programs/program-hover.jpg);">
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
                        <div style="background-image: url(assets/img/programs/program-hover.jpg);">
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
                        <div style="background-image: url(assets/img/programs/program-hover.jpg);">
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
    <!--====== End Programs Area ======-->

    <!--====== Start Payment System Section ======-->
    <section class="payment-section p-t-10 p-b-50 p-b-md-110">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-6">
                    <div class="preview-galley-v9">
                        <img src="{{ asset('public/assets/img/gold-section-3-compress-1.jpg') }}"
                            alt="{{ $section_3_heading }}" class="image-one">

                        <div class="icons">
                            <img src="{{ asset('public/assets/zeid/img/particle/wallet-icon-2.png') }}" alt="icon"
                                class="icon-one animate-float-bob-y">
                            <img src="{{ asset('public/assets/zeid/img/particle/wallet-icon.png') }}" alt="icon"
                                class="icon-two animate-zoominout">
                            <img src="{{ asset('public/assets/zeid/img/particle/wallet-icon-3.png') }}" alt="icon"
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
    <section class="payment-section p-t-50 p-b-50 p-b-md-110">
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
                            <!--  <li>-->
                            <!--<h4 class="title">{{ $section_4_subheading_4 }}</h4>-->
                            <!--<p>{{ $section_4_description_4 }}</p>-->
                            <!--  </li>-->
                        </ul>
                        <a href="#registration_section" class="template-btn bordered-body-4 m-t-40">
                            DAFTAR SEKARANG
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 col-md-8 mt-5">
                    <div class="benefit-preview-images">
                        <div class="image-two animate-float-bob-y">
                            <img src="{{ asset('public/assets/img/gold-section-4-compress-2.jpg') }}"
                                alt="{{ $section_4_heading }}">
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
    <footer class="template-footer footer-bordered ">
        <div class="container">
            <div class="footer-widgets">
                <div class="row">
                    <!-- Single Footer Widget -->
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="widget text-block-widget">
                            <img src="{{ asset('public/assets/img/tpfx/logo12.png') }}" alt="logo tpfx">
                            <p>Sahid Soedirman Center, Lt. 20A/E,
                                Jl. Jenderal Sudirman No 86,
                                Kel. Karet Tengsin, Kec. Tanah Abang,
                                Kota Adm. Jakarta Pusat, Prov. DKI Jakarta,
                                Kode Pos 10220</p>

                        </div>
                    </div>
                    <!-- Single Footer Widget -->
                    <div class="col-lg-3 col-md-6 col-sm-6 p-t-80">
                        <div class="d-lg-flex justify-content-center">
                            <div class="widget nav-widget">
                                <h5 class="widget-title">Hubungi Kami</h5>
                                <ul>
                                    <li>Email: support@tpfx.co.id</li>
                                    <li>Call Center: (+62)21 252 75 77</li>
                                    <li>Senin - Jum’at (09.00 - 17.00)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </footer>

    <!--====== Start Footer ======-->
    <footer class="template-footer footer-bordered">
        <div class="container">
            <p style="text-align: center;font-size: 10px;padding-left: 50px;padding-right: 50px;color: white;">WASPADA
                PENIPUAN MENGATASNAMAKAN PT TRIJAYA PRATAMA FUTURES ATAU TPFX MELALUI WEBSITE XTB668.COM ATAU SEJENISNYA

                Segala informasi resmi dimuat pada website resmi TPFx www.tpfx.co.id dan call center resmi di nomor
                (+62)21 252 75 77</p>

            <p style="text-align: center;font-size: 10px;padding-left: 50px;padding-right: 50px;color: white;">
                </br>Trading
                derivatif yang mengandung sistem margin membawa keuntungan tinggi terhadap dana, tetapi juga dapat
                memberikan kerugian atas seluruh margin yang diperdagangkan. Pastikan anda benar-benar memahami resiko
                Trading derivatif dan mintalah nasihat consultant jika diperlukan. PT Trijaya Pratama Futures tidak
                bertanggung jawab atas segala bentuk kerugian.</p>

            <p style="text-align: center;font-size: 10px;padding-left: 50px;padding-right: 50px;color: white;">
                </br>Semua produk keuangan yang diperdagangkan menggunakan sistem margin melibatkan risiko tinggi untuk
                dana Anda. Produk keuangan ini tidak cocok untuk semua investor dan Anda mungkin kehilangan lebih dari
                deposit awal Anda. Pastikan Anda sepenuhnya memahami risikonya dan mencari nasihat independen jika
                perlu.</p>
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

    <div class="modal fade" id="formSubmittedModal" tabindex="-1" role="dialog" aria-labelledby="formSubmittedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title text-dark text-sm-center" id="formSubmittedModalLabel">REGISTRASI BERHASIL</h5>
              <button type="button" id="closeFormSubmittedModal2" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body text-center">
                @if (request()->get('utm_campaign') == 'ebook')
                <button id="downloadEbookModal" class="btn btn-warning my-4 me-1 me-sm-4 mx-auto text-center">DOWNLOAD E-BOOK</button>
                @elseif (request()->get('utm_campaign') == 'ebooktc')
                <button id="downloadEbookTCModal" class="btn btn-warning my-4 me-1 me-sm-4 mx-auto text-center">DOWNLOAD E-BOOK TC</button>
                @else
                Anda Akan Segera Dihubungi Oleh Tim Representatif Kami
                @endif
            </div>
            <div class="modal-footer">
              <button type="button" id="closeFormSubmittedModal3" class="btn btn-lg btn-warning" data-dismiss="modal">Kembali ke Website</button>
              <button type="button" id="closeFormSubmittedModal" class="btn btn-lg btn-warning" data-dismiss="modal"><i class="fa fa-brands fa-whatsapp"></i>Hubungi Kami</button>
            </div>
          </div>
        </div>
      </div>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!--====== Jquery ======-->
    <script src="{{ asset('public/assets/zeid/js/jquery-3.6.0.min.js') }}"></script>
    <!--====== Bootstrap ======-->
    <script src="{{ asset('public/assets/zeid/js/bootstrap.min.js') }}"></script>
    <!--====== Magnific ======-->
    <script src="{{ asset('public/assets/zeid/js/jquery.magnific-popup.min.js') }}"></script>
    <!--====== Isotope Js ======-->
    <script src="{{ asset('public/assets/zeid/js/isotope.pkgd.min.js') }}"></script>
    <!--====== Jquery UI Js ======-->
    <script src="{{ asset('public/assets/zeid/js/jquery-ui.min.js') }}"></script>
    <!--====== Inview ======-->
    <script src="{{ asset('public/assets/zeid/js/jquery.inview.min.js') }}"></script>
    <!--====== Nice Select ======-->
    <script src="{{ asset('public/assets/zeid/js/jquery.nice-select.min.js') }}"></script>
    <!--====== Wow ======-->
    <script src="{{ asset('public/assets/zeid/js/wow.min.js') }}"></script>
    <!--====== Main JS ======-->
    <script src="{{ asset('public/assets/zeid/js/main.js') }}"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#leadsForm").submit(function (e) {

            e.preventDefault();

            var name = $("input[name=name]").val();
            var phonenumber = $("input[name=phonenumber]").val();
            var email = $("input[name=email]").val();
            var source = $("input[name=source]").val();
            var status = $("input[name=status]").val();
            var utm_campaign = $("input[name=utm_campaign]").val();

            $.ajax({
                type: 'POST',
                url: "{{ route('events.store_leads') }}",
                data: {
                    name: name,
                    phonenumber: phonenumber,
                    email: email,
                    source: source,
                    status: status,
                    utm_campaign: utm_campaign
                },
                success: function (data) {
                    $('#formSubmittedModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                },
                async: false
            });

        });

        $('#closeFormSubmittedModal,#closeFormSubmittedModal2').click(function (){
            location.href='https://api.whatsapp.com/send?phone=6282228811102&text=Halo%2C%20Saya%20ingin%20info%20lebih%20lanjut%20mengenai%20Trading%20di%20TPfx.%20Bisa%20bantu%20Saya%3F';
        });
        
         $('#closeFormSubmittedModal3').click(function (){
            location.href='https://www.tpfx.co.id';
        });

        $('#downloadEbookModal').click(function (){
            window.open('https://api.whatsapp.com/send?phone=6282228811102&text=Halo%2C%20Saya%20ingin%20info%20lebih%20lanjut%20mengenai%20Trading%20di%20TPfx.%20Bisa%20bantu%20Saya%3F', '_blank');
            location.href='https://click.tpfx.co.id/public/assets/tpfx-ebook-baru.pdf';
        });
        $('#downloadEbookTCModal').click(function (){
            window.open('https://api.whatsapp.com/send?phone=6282228811102&text=Halo%2C%20Saya%20ingin%20info%20lebih%20lanjut%20mengenai%20Trading%20di%20TPfx.%20Bisa%20bantu%20Saya%3F', '_blank');
            location.href='https://click.tpfx.co.id/public/assets/tpfx-ebook-tc.pdf';
        });
    </script>
</body>

</html>
