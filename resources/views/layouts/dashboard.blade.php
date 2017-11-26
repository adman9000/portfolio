<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>The Trading Port</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>
<body >

        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        The Trading Port
                    </a>
                </div>


                <div class="collapse navbar-collapse" id="app-navbar-collapse">

                 @if (Auth::user())
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">

                        <li><a href="{{ route('coins') }}">Coins</a></li>
                        <li><a href="{{ route('exchanges') }}">Exchanges</a></li>
                         <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    Charts <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                         <li><a href="{{ route('charts') }}">All Time</a></li>
                         <li><a href="{{ route('charts24', '24hr') }}">24 Hour</a></li>
                         </ul></li>
                         
                        @can('autotrade')
                            <li><a href="{{ route('schemes') }}">Schemes</a></li>
                        @endcan
                        
                        @can('trade')
                        <li><a href="{{ route('transactions') }}">Transactions</a></li>
                        @endcan

                    </ul>
                    @endif

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('register') }}">Register</a></li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        @if (session('status-success'))
    <div class="alert alert-success">
        {{ session('status-success') }}
    </div>
@endif

@if (session('status-danger'))
    <div class="alert alert-danger">
        {{ session('status-danger') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif



        @yield('content')

<footer class='footer'>
    <div class='row'>
        <div class='col-xs-12'>
        <center><span ng-bind='last_updated'></span></center>
        </div>
    </div>

</footer>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/code.js') }}"></script>

<script src="{{ asset('js/custom.js') }}"></script>

 @yield('footer_scripts')

</body>
</html>
