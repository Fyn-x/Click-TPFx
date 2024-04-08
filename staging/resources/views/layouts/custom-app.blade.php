<!doctype html>
<html lang="en" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="TPFx Rebate">
    <meta name="author" content="TPFx">

    <!-- title -->
    <title>TPFx - Check Leads</title>

   <link rel="shortcut icon" type="image/x-icon" href="{{asset('public/assets/img/favicon-32x32.png')}}" />

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="{{asset('public/assets/leads/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="{{asset('public/assets/leads/css/style.css')}}" rel="stylesheet" />
    <link href="{{asset('public/assets/leads/css/dark-style.css')}}" rel="stylesheet" />
    <link href="{{asset('public/assets/leads/css/transparent-style.css')}}" rel="stylesheet">
    <link href="{{asset('public/assets/leads/css/skin-modes.css')}}" rel="stylesheet" />

</head>

    <body class="">

        @yield('class')

            <!-- global-loader -->
          
            <!-- global-loader closed -->

                <!-- PAGE -->
                <div class="page">
                    <div class="">

                        @yield('content')

                    </div>
                </div>
                <!-- End PAGE -->

        </div>
        <!-- BACKGROUND-IMAGE CLOSED -->

   

    </body>

</html>
