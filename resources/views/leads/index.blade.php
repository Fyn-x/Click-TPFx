<link href="{{asset('assets/leads/css/style.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/leads/css/dark-style.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/leads/css/transparent-style.css')}}" rel="stylesheet">
    <link href="{{asset('assets/leads/css/skin-modes.css')}}" rel="stylesheet" />
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/img/favicon-32x32.png')}}" />
    <script src="{{asset('assets/leads/plugins/bootstrap/js/popper.min.js')}}"></script>
    <script src="{{asset('assets/leads/plugins/bootstrap/js/bootstrap.min.js')}}"></script>
<head>
    <title>Check Leads</title>
   <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
</head>
 <!--   <form action="/leads/search" method="GET">-->
	<!--	<input type="text" name="email_search" placeholder="Cari Email" value="{{ old('email_search') }}">-->
	<!--	<input type="submit" value="CARI">-->
	<!--</form>-->
	<!--<form action="/leads/search" method="GET">-->
	<!--	<input type="text" name="source_search" placeholder="Cari Source" value="{{ old('source_search') }}">-->
	<!--	<input type="submit" value="CARI">-->
	<!--</form>-->
<body>
<div class="app-header header sticky">
                <div class="container-fluid main-container">
                    <div class="d-flex">
                        <!--<a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="javascript:void(0)"></a>-->
                        <!-- sidebar-toggle-->

                        <!-- LOGO -->
                        <div class="main-header-center ms-3 d-none d-lg-block">
                            <!--<input class="form-control" placeholder="Search for results..." type="search">-->
                            <!--<button class="btn px-0 pt-2"><i class="fe fe-search" aria-hidden="true"></i></button>-->
                        </div>
                        <div class="d-flex order-lg-2 ms-auto header-right-icons">
                            <div class="dropdown d-none">
                                <!--<a href="javascript:void(0)" class="nav-link icon" data-bs-toggle="dropdown">-->
                                <!--    <i class="fe fe-search"></i>-->
                                <!--</a>-->
                                <div class="dropdown-menu header-search dropdown-menu-start">
                                    <div class="input-group w-100 p-2">
                                        <input type="text" class="form-control" placeholder="Search....">
                                        <div class="input-group-text btn btn-primary">
                                            <i class="fe fe-search" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- SEARCH -->
                            <button class="navbar-toggler navresponsive-toggler d-lg-none ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent-4" aria-controls="navbarSupportedContent-4" aria-expanded="false" aria-label="Toggle navigation">
									<span class="navbar-toggler-icon fe fe-more-vertical"></span>
								</button>
                            <div class="navbar navbar-collapse responsive-navbar p-0">
                                <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                                    <div class="d-flex order-lg-2">
                                        <div class="dropdown d-lg-none d-flex">
                                            <a href="javascript:void(0)" class="nav-link icon" data-bs-toggle="dropdown">
                                                <i class="fe fe-search"></i>
                                            </a>
                                            <div class="dropdown-menu header-search dropdown-menu-start">
                                                <div class="input-group w-100 p-2">
                                                    <input type="text" class="form-control" placeholder="Search....">
                                                    <div class="input-group-text btn btn-primary">
                                                        <i class="fa fa-search" aria-hidden="true"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- COUNTRY -->

                                        <!-- SEARCH -->

                                        <!-- FULL-SCREEN -->

                                        <!-- NOTIFICATIONS -->
                                        <div class="dropdown d-flex profile-2">
                                            <a href="javascript:void(0)" data-bs-toggle="dropdown" class="nav-link leading-none d-flex">
                                                <img src="{{asset('public/assets/leads/images/web/codeigniter.png')}}" alt="profile-user" class="avatar  profile-user brround cover-image">
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-arrow" style="margin-left: -125px">
                                                <div class="drop-heading">
                                                    <div class="text-center">
                                                        <h5 class="text-dark mb-0 fs-14 fw-semibold"> {{ auth()->user()->name }}</h5>
                                                        <small class="text-muted">{{ auth()->user()->email }}</small>
                                                    </div>
                                                </div>
                                                <div class="dropdown-divider m-0"></div>
                                                <form method="POST" action="{{ url('logout') }}">
                                        @csrf
                                        <button class="dropdown-item" type="submit">
                                            <i class="dropdown-icon fe fe-alert-circle"></i> Sign out
                                        </button>
                                    </form>
                                            </div>
                                        </div>
                                        <!-- MESSAGE-BOX -->

                                        <!-- SIDE-MENU -->

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

 <div class="main-container container-fluid">
     <div class="card mt-2">
          <div class="card-header">
                <h1 class="card-title">Leads</h1>
            </div>
    <div class="card-body">
	<div class="container mt-5">
    <!--<h2 class="mb-4"></h2>-->

    <div class="d-flex justify-content-between align-items-center my-5">
        <div class="col-6">
            <strong>Date Filter:</strong>
            <input style="width: 200px" type="text" name="daterange" value="" />
            <button class="btn btn-success filter">Filter</button>
        </div>
        <div class="d-flex flex-column justify-content-end">
            <a href="{{ route('leads.upload_leads_view') }}" target="_blank" class="btn btn-dark">Upload Leads</a>
            <a href="#" id="export-excel" target="_blank" class="btn btn-secondary  my-2">Export to Excel</a>
        </div>
    </div>

    <table class="table-responsive table-bordered yajra-datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Source</th>
                <th>Medium</th>
                <th>Campaign</th>
                <th>Created Leads</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
</div>
</div>
</div>
</div>
</body>


<script type="text/javascript">
$(function () {
     $('input[name="daterange"]').daterangepicker({
        startDate: moment().subtract(3, 'Y'),
        // startDate: moment().subtract(1, 'months').startOf('month'),
        endDate: moment()
    });

    var table = $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
        url:"{{ route('leads.getLeads') }}",
        data:function (d) {
                d.from_date = $('input[name="daterange"]').data('daterangepicker').startDate.format('YYYY-MM-DD 00:00:00');
                d.to_date = $('input[name="daterange"]').data('daterangepicker').endDate.format('YYYY-MM-DD 23:59:59');
            }
        },
        // lengthMenu: [10, 20, 50],
        lengthChange: false,


        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'phone', name: 'phone'},
            {data: 'source', name: 'source'},
            {data: 'medium', name: 'medium'},
            {data: 'campaign', name: 'campaign'},
            {data: 'created_at', type: 'num'},
        ],

    });
     $(".filter").click(function(){
        table.draw();
    });

    $('#export-excel').click(function(){
        const start = $('input[name="daterange"]').data('daterangepicker').startDate.format('YYYY-MM-DD 00:00:00');
        const end = $('input[name="daterange"]').data('daterangepicker').endDate.format('YYYY-MM-DD 23:59:59');
        const search = table.search()
        const newUrl = `/exports?start=${start}&end=${end}&search=${search}`

        $(this).attr("href", newUrl);
        window.open($(this).attr("href"));
    })

  });

</script>





