<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en">
<!--<![endif]-->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    @laravelPWA
    <title>BISMO</title>
    <meta name="description" content="BISMO ALSUPER">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="{{ asset('/grubsaico.png')}}" />

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{ asset('https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i')}}" rel="stylesheet">
    @yield('css') 
    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('css/admin-custom.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/bootstrap-select.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-multiselect.css')}}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/datatables_cdn.css')}}"/>
    <style type="text/css">
      .oculto{
        display: none;
      }
    </style>
</head>

<body>
  <!-- Left Panel -->
  <div id="wrapper">
    @if (Auth::check()) 
    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion 
    " id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('welcome') }}">
        <div class="sidebar-brand-icon rotate-n-15">
          <i class="fas fa-draw-polygon"></i>
        </div>
        <div class="sidebar-brand-text mx-3">AL SUPER</div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <!-- Nav Item - Dashboard -->
      <li class="nav-item active">
        <a class="nav-link" href="{{ route('welcome') }}">
          <span>Dashboard</span></a>
      </li>
      <!-- Divider -->
      <hr class="sidebar-divider">
        <!-- Nav Item - inmuebles -->  
        @if (auth()->user()->rol == 3)
          <li class="nav-item">
              <a class="nav-link" href="{{ route('propiedades') }}"><span> Polizas excel</span></a>
          </li>
          <li class="nav-item">
              <a class="nav-link" href="{{ route('poliza_ws') }}"><span> Polizas a WS</span></a>
          </li>
          <li class="nav-item">
              <a class="nav-link" href="{{ route('propiedades') }}"><span> Descargar los documentos</span></a>
          </li>
          <li class="nav-item">
              <a class="nav-link" href="{{ route('propiedades') }}"><span> Pagos referenciados</span></a>
          </li>
          <li class="nav-item">
              <a class="nav-link" href="{{ route('propiedades') }}"><span> PLD</span></a>
          </li>
        @endif
      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>

    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>

          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">
            <div class="topbar-divider d-none d-sm-block"></div>
            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">@auth
                  {{ auth()->user()->name }}
                @endauth</span>
                @if (!empty(auth()->user()->foto_perfil))
                    <img class="img-profile rounded-circle" src="{{ asset(auth()->user()->foto_perfil) }}" alt="User">
                @else
                    <img class="img-profile rounded-circle" src="{{ asset('images/iconos/boss.png') }}" alt="User">
                @endif              </a>
              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('usuarios-profile',['id'=> auth()->id()]) }}">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Profile
                </a>
                <div class="dropdown-divider"></div>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Cerrar sesion</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>

              </div>
            </li>

          </ul>

        </nav>
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">

          <!-- Page Heading -->
          <div class="row justify-content-between mb-4" id="page-top">
            <div class="col-md-8">
              <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
            </div>
            <div class="col-md-4 h3 mb-0 " align="right">
              @yield('filter')
            </div>
          </div>

          <!-- Content Row -->
          @yield('content') 
          <!-- END Content Row -->
        </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->

      <!-- Footer -->
      <footer class="sticky-footer bg-white" id="page-bottom">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>Copyright &copy; NEXTAPP <?php echo date('Y') ?></span>
          </div>
        </div>
      </footer>
      <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->
    @endif
  </div>
  <!-- End of Page Wrapper -->

  <!-- Right Panel -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
  <i class="fas fa-angle-up"></i>
  </a>
  <!-- Bootstrap core JavaScript-->
  <script src="{{ asset('vendor/jquery/jquery.min.js')}}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

  <!-- Core plugin JavaScript-->
  <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js')}}"></script>
  <!-- Page level plugins -->
  <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
  <script type="text/javascript" src="{{ asset('js/bootstrap-multiselect.js') }}"></script>
  <script type="text/javascript" src="{{ asset('js/bootstrap-select.min.js') }}"></script>
  <!-- Custom scripts for all pages-->
  <script src="{{ asset('js/sb-admin-2.min.js')}}"></script>
  <script src="{{ asset('js/jquery.rwdImageMaps.min.js')}}"></script>
  <script src="{{ asset('vendor/chart.js/Chart.min.js')}}"></script>
  <script src="{{ asset('js/maphilight-master/jquery.maphilight.js')}}"></script>
  <script type='text/javascript' src="{{ asset('https://rawgit.com/RobinHerbots/jquery.inputmask/3.x/dist/jquery.inputmask.bundle.js') }}"></script>
  <script>
        function sizeWindow(){
            var widthW =  $(window).width();
            var tablet = 992;
            var movil = 768;
            if(widthW < tablet && widthW > movil){
            $('#accordionSidebar').removeClass('toggled');
            
            }else if(widthW > tablet){
            $('#accordionSidebar').removeClass('toggled');
            }else if(widthW < movil){
            $('#accordionSidebar').addClass('toggled');
            }
        }

        $(document).ready(function() {
            sizeWindow()
        }); 

        $(window).resize(function() {
            sizeWindow()
        }); 
    </script>
  <script>
    jQuery(document).ready(function($)
    {
      //enmacrar los input tio text con la clase mask
      $(".mask").inputmask({ 'alias': 'decimal', 'groupSeparator': ',', 'autoGroup': true, 'digits': 2, 'digitsOptional': false, 'placeholder': '0.00'}); 

      $('img[usemap]').rwdImageMaps();
      $('#slippry-slider').slippry(
        defaults = {
          transition: 'vertical',
          useCSS: true,
          speed: 5000,
          pause: 3000,
          initSingle: false,
          auto: true,
          preload: 'visible',
          pager: false,
        }
      )
    });
  </script>
  <script>
    $(function() {
        $.fn.maphilight.defaults = {
            fill: true,
            fillColor: '71FF33',
            fillOpacity: 0.2,
            stroke: true,
            strokeColor: '7D1612',
            strokeOpacity: 0.5,
            strokeWidth: 1,
            fade: true,
            alwaysOn: false,
            neverOn: false,
            groupBy: false,
            wrapClass: true,
            shadow: false,
            shadowX: 0,
            shadowY: 0,
            shadowRadius: 6,
            shadowColor: '71FF33',
            shadowOpacity: 0.8,
            shadowPosition: 'outside',
            shadowFrom: false
        }
        $('.map').maphilight({
      
        });
       
    });
  </script>
  @yield('scripts')
  @stack('scripts')
</body>

</html>
