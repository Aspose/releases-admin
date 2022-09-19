<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name=robots content="noindex, nofollow">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Containerize.Releases Backend') }}</title>

    <!-- Styles -->
    <!-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> -->
    <link href="{{ env('APP_URL') }}/css/bootstrap.css" rel="stylesheet">
    <link href="{{ env('APP_URL') }}/css/site.css" rel="stylesheet">
    <link href="{{ env('APP_URL') }}/css/bootstrap-responsive.css" rel="stylesheet">
    <script src="{{ env('APP_URL') }}/js/app.js" defer></script>
    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body>
    <div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container-fluid">
                <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <a href="#" id="ctl00_lnkMain" class="brand"> {{ config('app.name', 'Containerize.Releases Backend') }} </a>
                <div class="btn-group pull-right">
                    @if (Auth::guest())
                    
                    @else
                    <a class="btn" href="#"><i class="icon-user"></i>
                        <span id="ctl00_lblFullName">{{ Auth::user()->name }}</span></a>
                    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a></li>
                                                     <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                    </ul>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span3">
                
            </div>
            <div class="span4" style="text-align: center">
                @yield('content')
            </div>
            <div class="span3">
                
            </div>
        </div>
        <hr />
        <footer class="well">&copy; Containerize.Releases Backend - 2022 </footer>
    </div>
    <!-- Scripts -->
    <script src="{{ env('APP_URL') }}/js/app.js"></script>
    <script src="{{ env('APP_URL') }}/js/jquery.js"></script>
    <script src="{{ env('APP_URL') }}/js/bootstrap.min.js"></script>
    <script src="{{ env('APP_URL') }}/js/bootstrap-wysiwyg.js"></script>
    <script src="{{ env('APP_URL') }}/js/canvasjs.min.js"></script>
    <script src="{{ env('APP_URL') }}/js/ChartHelper.js"></script>
    <script src="{{ env('APP_URL') }}/js/ReportController.js"></script>
</body>
</html>
