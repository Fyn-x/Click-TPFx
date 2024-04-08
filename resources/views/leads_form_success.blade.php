<!DOCTYPE html>
<html lang="en">

<head>

    <!-- meta tags -->
    <meta charset="utf-8">
    <meta name="keywords"
        content="bootstrap 5, premium, multipurpose, sass, scss, saas, rtl, business, consulting, accounting" />
    <meta name="description" content="Consulting Finance Accounting HTML5 Template" />
    <meta name="author" content="Trijaya Pratama Futures" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Title -->
    <title>{{ config('app.name') }}</title>

    <!-- favicon icon -->
    <link rel="icon" type="image/png" href="{{ asset('public/assets/img/favicon-32x32.png') }}">

    <!-- inject css start -->

    <!--== bootstrap -->
    <link href="{{ asset('public/assets/thank-you/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />

    <!--== style -->
    <link href="{{ asset('public/assets/thank-you/style.css') }}" rel="stylesheet" type="text/css" />

    <!--== responsive -->
    <link href="{{ asset('public/assets/thank-you/responsive.css') }}" rel="stylesheet" type="text/css" />

    <!-- inject css end -->

    <style>
        .header-wrap {
            position: relative;
            background: #ffbf3f;
        }

        .logo img {
            height: 70px;
        }

        .loader {
            width: 700px;
            position: initial;
        }

        .loader::after {
            content: "Trijaya Pratama Futures";
        }

        .loader-mobile::after {
            content: "TPF";
        }
    </style>

    <meta name="facebook-domain-verification" content="diuhpyqhjrcd32phqlrct3nudrucem" />
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-WWQKGDV');</script>
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
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WWQKGDV"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->

    <div class="page-wrapper">

        <!-- preloader start -->

        <div id="ht-preloader">
            <div class="clear-loader">
                <div class="loader"></div>
            </div>
        </div>

        <!-- preloader end -->

        <!--body content start-->

        <div class="page-content">

            <!--coming soon start-->

            <section class="fullscreen-banner p-0">
                <div class="container h-100">
                    <div class="row h-100">
                        <div class="col-12 text-center h-100 d-flex align-items-center justify-content-center">
                            <div><img class="img-fluid d-block mb-5 mx-auto"
                                    src="{{ asset('public/images/logo-tpfx-black-small.png') }}" alt="">
                                <h2>REGISTRASI BERHASIL</h2>
                                <h6 class="text-theme">Anda akan segera dihubungi oleh pihak representatif dari kami
                                </h6>
                                <p>Silakan klik <a target="_blank" href="https://tpfx.co.id">link</a> berikut untuk
                                    kembali ke website.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!--coming soon end-->

        </div>

        <!--body content end-->

    </div>

    <!-- page wrapper end -->


    <!--back-to-top start-->

    <div class="scroll-top"><a class="smoothscroll" href="#top"><i class="flaticon-top"></i></a></div>

    <!--back-to-top end-->


    <!--== jquery -->
    <script src="{{ asset('public/assets/thank-you/theme.js') }}"></script>

    <!--== theme-plugin -->
    <script src="{{ asset('public/assets/thank-you/theme-plugin.js') }}"></script>

    <!--== theme-script -->
    <script src="{{ asset('public/assets/thank-you/theme-script.js') }}"></script>

    <!-- inject js end -->

    <script>
        $(function () {
            var count = 5;
            var countdown = setInterval(function () {
                $("#countdown").html(count);
                if (count == 0) {
                    clearInterval(countdown);
                    window.open('https://www.tpfx.co.id/', "_self");

                }
                count--;
            }, 1000);
        });
    </script>

</body>

</html>
