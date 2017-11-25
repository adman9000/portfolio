<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $meta_title }}</title>

    <!-- Styles -->
    <link rel="stylesheet" href="{!! asset('css/vendor.css') !!}" />
    <link rel="stylesheet" href="{!! asset('css/inspinia.css') !!}" />
</head>
<body>

    <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                                    {{ csrf_field() }}
                                                </form>

     <div id="wrapper">

        <nav class="navbar-default navbar-static-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav metismenu" id="side-menu">
                    <li class="nav-header">

                        <div class="dropdown profile-element">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold">{{ Auth::user()->name }}</strong>
                             </span> <span class="text-muted text-xs block">My Links <b class="caret"></b></span> </span> </a>
                            <ul class="dropdown-menu animated fadeIn m-t-xs">
                                <li><a href="/admin/mydetails">My Details</a></li>
                                <li><a href="{{ url('/logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a></li>
                            </ul>
                    </div>
                    <div class="logo-element">
                    </div>

                    </li>
                         <li class="{{ isActiveRoute('admin') }}" ><a href="{{ route('admin') }}"><i class="fa fa-th-large"></i> <span class="nav-label">Dashboard</span></a></li>

                        @can('edit users')
                             <li class="{{ isActiveRoute('users') }}" ><a href="{{ route('users') }}"><i class="fa fa-th-large"></i> <span class="nav-label">User Management</span></a></li>
                        @endcan

                        @can('edit content')
                             <li class="{{ isActiveRoute('content') }}" ><a href="{{ route('content') }}"><i class="fa fa-th-large"></i> <span class="nav-label">Content Management</span></a></li>
                        @endcan
                </ul>

            </div>
        </nav>

         <!-- Page wraper -->
        <div id="page-wrapper" class="gray-bg">

            <div class="row border-bottom">
                <nav class="navbar navbar-static-top white-bg" role="navigation" style="margin-bottom: 0">
                    <div class="navbar-header">
                        <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
                        <form role="search" class="navbar-form-custom form-inline" action="/admin/home/list" id="searchform">
                             {{ csrf_field() }}
                        <input type="hidden" name="object_type" id='search_object_type'>
                        <input type="hidden" name="object_id" id='search_object_id' >
                        <table><tr><td>
                        <div class="form-group" >
                            <input type="text" placeholder="Search for something..." class="form-control typeahead-search" name="keyword" id="">
                             </div>
                             </td><td>
                            <button class="btn btn-primary">Search</button></td></tr></table>
                       
                    </form>
                    </div>
                     <!-- Right Side Of Navbar -->
                            <ul class="nav navbar-top-links navbar-right">
                               
                                            <li>
                                                <a href="{{ url('/logout') }}"
                                                    onclick="event.preventDefault();
                                                             document.getElementById('logout-form').submit();">
                                                    Logout
                                                </a>

                                               
                                    </li>
                            </ul>
                </nav>
            </div>

            <div class="wrapper wrapper-content animated fadeInRight">

                <? //Only dashboard doesnt have breadcrumbs or this header section
                if(sizeof($breadcrumb_array)>0) { ?>
            
                <div class="row">
                    <div class="col-sm-12">
                        <div class="white-bg padding-small border-bottom">
                        
                            <?=$activity_link?>
                            
                            <a href='/admin/home' class='shortcut-page' data-async data-post="action=add_shortcut&url=/<?=request()->path()?>&title=<?=$meta_title?>" data-onsuccess='setWarning' data-toggle="tooltip" data-placement="left" title="Shortcut this page" ><span class="glyphicon glyphicon-star <?=$page_shortcut ? "text-warning" : ""?>"></span></a>
                            <h1 class="margin-remove margin-bottom-small"><?=$page_title?><?=$page_subtitle ? ' - <span class="sub-header">'.$page_subtitle.'</span>' : ''?></h1>
                            <? // need a breadcrumb builder ?>
                            <ol class="breadcrumb">
                                <? foreach($breadcrumb_array as $i=>$crumb) { 
                                    if($i == sizeof($breadcrumb_array)-1) { ?>
                                        <li class='active'>
                                            <strong><?=$crumb['title']?></strong>
                                        </li>
                                    <? } else { ?>
                                        <li>
                                            <a href="<?=$crumb['url']?>" ><?=$crumb['title']?></a>
                                        </li>
                                    <? } ?>
                                <? } ?>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <? } ?>

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

            </div>

          <div class="footer">
            <div class="pull-right">
                <a target="_blank" href="http://www.diligencedev.co.uk">Diligence Support</a>
            </div>
            <div>
                <strong>Created by Diligence Group Ltd</strong>
            </div>
        </div>


        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/admin.js') }}"></script>

    @yield('footer-scripts')

</body>
</html>
