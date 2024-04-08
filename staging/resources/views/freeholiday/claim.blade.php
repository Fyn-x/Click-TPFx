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
    <title>TPFx - Free Holiday Claim</title>
    <!--====== Favicon Icon ======-->
    <link rel="shortcut icon" href="/public/assets/img/favicon.png" type="img/png" />

    <link rel="icon" type="image/png" href="/public/assets/img/favicon-32x32.png">
    <!-- all css -->
    <link rel="stylesheet" href="/public/assets/zeid/css/all.min.css" media="all" defer />
    <link rel="stylesheet" href="/public/assets/zeid/css/style.css" media="all" defer />
    <link rel="stylesheet" href="/public/assets/zeid/fonts/fontawesome/css/all.min.css" media="all" defer />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="/public/assets/zeid/css/plugins/datetimepicker/css/classic.css" rel="stylesheet" />
    <link href="/public/assets/zeid/css/plugins/datetimepicker/css/classic.time.css" rel="stylesheet" />
    <link href="/public/assets/plugins/datetimepicker/css/classic.date.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="/public/assets/zeid/css/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css">
    <style>
        .selection {
            width: 100%;
            display: block;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            height: 55px;
            background-color: var(--color-white);
            font-size: 15px;
            font-style: italic;
            padding: 10px 25px;
            -webkit-transition: 0.3s;
            -o-transition: 0.3s;
            transition: 0.3s;
        }
    </style>

</head>

<body class="e-wallet-landing">

    <!--====== Start Header ======-->
    <header class="template-header navbar-center absolute-header nav-border-bottom sticky-header">
        <div class="container-fluid">
            <div class="header-inner">
                <div class="header-left">
                    <div class="branding-and-language-selection branding-border-right">
                        <div class="brand-logo">
                            <a href="https://tpfx.co.id">
                                <img src="/public/assets/img/tpfx/logo12.png" alt="logo tpfx">
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

        </div>
    </section>
    <!--====== End Hero Area ======-->

    <!--====== Booking Form Start ======-->
    <section class="booking-section p-t-150 p-b-50" id="registration_section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="row align-items-center justify-content-center">


                        <div class="col-lg-12 col-md-12">
                            <div class="booking-form wow fadeInUp ml-xl-3">
                                <h4 class="form-title text-center">Free Holiday Claim</h4>
                                <form id="leadsForm" role="form" method="POST"
                                    action="{{ route('freeholiday.claim_store') }}">
                                    @csrf
                                    @if(Session::has('errors'))
                                    <div class="alert alert-danger">
                                        {{Session::get('errors')}}
                                    </div>
                                    @endif
                                    @if(Session::has('success'))
                                    <div class="alert alert-success">
                                        {{Session::get('success')}}
                                    </div>
                                    @endif
                                    <div class="form-group">
                                        <label for="name">Nama Lengkap Nasabah</label>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}"
                                            placeholder="Ex: Gloria" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="account">ID Account</label>
                                        <input type="text" class="form-control" id="account" name="account" value="{{ old('account') }}"
                                            placeholder="Ex: 42808567" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="account_type">Account Type</label>
                                        <input type="text" id="account_type" class="form-control" name="account_type" value="{{ old('account_type') }}"
                                            placeholder="Ex: Standard Variable" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="new_account">Account Baru/Lama</label>
                                        <select class="form-select" name="new_account">
                                            <option value="1">Account Baru</option>
                                            <option value="0">Account Lama</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">No Telp Nasabah</label>
                                        <input type="text" class="form-control" id="phone" aria-describedby="phoneHelp"
                                            name="phone" placeholder="Ex: 088888888" pattern="(08)\d{8,14}" value="{{ old('phone') }}"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email Nasabah</label>
                                        <input type="email" class="form-control" id="email" aria-describedby="emailHelp"
                                            name="email" placeholder="Ex: Gloria@gmail.com" value="{{ old('email') }}"
                                            pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="deposit">Deposit</label>
                                        <input type="text" class="form-control" id="deposit" name="deposit" value="{{ old('deposit') }}"
                                            placeholder="Ex: 500" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="endyear_packages_id">Target Package</label>
                                        <select id="endyear_packages_id" class="form-custom-select" name="endyear_packages_id" style="width:100%">
                                            @foreach($packages as $package)
                                            <option value="{{ $package->id }}">Rp {{ $package->min }}-{{ $package->max }} juta | Min. Deposit ${{ $package->deposit }} | Min. Lot {{ $package->lot }} | Durasi {{ $package->duration }} bulan</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="date_enroll">Tanggal Enroll</label>
                                        <input type="date" id="date_enroll" class="form-control datepicker" name="date_enroll" value="{{ old('date_enroll') }}" required />
                                    </div>
                                    <div class="form-group">
                                        <label for="sales_name">Nama Sales</label>
                                        <input type="text" class="form-control" id="sales_name" name="sales_name" value="{{ old('sales_name') }}"
                                            placeholder="Ex: Gloria" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="name_receiver">Nama Penerima Reward</label>
                                        <input type="text" class="form-control" id="name_receiver" name="name_receiver"
                                            placeholder="Ex: Gloria" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="address_receiver">Alamat Penerima Reward</label>
                                        <input type="text" class="form-control" id="address_receiver" name="address_receiver"
                                            placeholder="Ex: jl. Kemangi No.101 Menteng Jakarta Pusat " required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_receiver">No Telp Penerima Reward</label>
                                        <input type="text" class="form-control" id="phone_receiver" aria-describedby="phoneHelp"
                                            name="phone_receiver" placeholder="Ex: 088888888" pattern="(08)\d{8,14}"
                                            value="{{ old('phone_receiver') }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_sales">No WA Sales yg Aktif</label>
                                        <input type="text" class="form-control" id="phone_sales" aria-describedby="phoneHelp"
                                        name="phone_sales" placeholder="Ex: 088888888" pattern="(08)\d{8,14}"
                                        value="{{ old('phone_sales') }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="name_spv">Nama SPV dan Kantor Cabang</label>
                                        <input type="text" class="form-control" id="name_spv" name="name_spv"
                                            placeholder="Ex: Gloria (Jakarta)" required>
                                    </div>
                                    <button id="leadsFormSubmit" type="submit" class="d-none"></button>
                                    <div class="text-center">
                                        <button class="g-recaptcha btn btn-lg btn-warning"
                                            data-sitekey="6LcwJpQeAAAAAEsB9mnzuItB94EZqHIXV2eVjTxt"
                                            data-callback='onSubmit' data-action='submit'>
                                            <span class="btn-text">Submit</span>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <!--====== Bootstrap ======-->
    <script src="{{ asset('/public/assets/zeid/js/bootstrap.min.js') }}"></script>
    <!--====== Magnific ======-->
    <script src="{{ asset('/public/assets/zeid/js/jquery.magnific-popup.min.js') }}"></script>
    <!--====== Isotope Js ======-->
    <script src="{{ asset('/public/assets/zeid/js/isotope.pkgd.min.js') }}"></script>
    <!--====== Jquery UI Js ======-->
    <script src="{{ asset('/public/assets/zeid/js/jquery-ui.min.js') }}"></script>
    <!--====== Inview ======-->
    <script src="{{ asset('/public/assets/zeid/js/jquery.inview.min.js') }}"></script>
    <!--====== Nice Select ======-->
    <script src="{{ asset('/public/assets/zeid/js/jquery.nice-select.min.js') }}"></script>
    <!--====== Wow ======-->
    <script src="{{ asset('/public/assets/zeid/js/wow.min.js') }}"></script>
    <!--====== Main JS ======-->
    <script src="{{ asset('/public/assets/zeid/js/main.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function () {
            $('.form-custom-select').select2({
                width: "resolve"
            });
        });
    </script>
</body>

</html>
