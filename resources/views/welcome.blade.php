@extends('layouts.main')

@section('content')
   

  <div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">The Trading Port</div>

                <div class="panel-body">

                    @if (Auth::check())
                       
                       Hi {{Auth::user()->name}}

                    @else

                        <a href="{{ url('/login') }}">Login</a> or <a href="{{ url('/register') }}">Register</a>
                        
                    @endif

                </div>

            </div>

        </div>
    </div>


    
    @endsection