@extends('layouts.main')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                @if (Auth::check())
                       
                       Hi {{Auth::user()->name}}

                         <p>
                    @hasanyrole('member|administrator')
                        I am either a member or an admin or both!
                    @else
                        I am a nobody
                    @endhasanyrole
                    </p>
                    <p>
                    @role('member')
                        I am a member!
                    @else
                        I am not a member...
                    @endrole
                </p>
                <p>
                    @role('administrator')
                        <a href='{{ route('admin') }}'>Admin</a>
                    @else
                        Not an admin
                    @endrole
                </p>

                    @else

                        <a href="{{ url('/login') }}">Login</a> or <a href="{{ url('/register') }}">Register</a>
                        
                    @endif



                </div>
            </div>
        </div>
    </div>
</div>
@endsection
