@extends('layouts.dashboard')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">

                   @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

        </div>
    </div>

   <div class='row'>

        <div class='col-xs-12 col-sm-6'>


            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class='panel-title pull-left'>Your Profile</h2>
                     
                    <div class='clearfix'></div>
                </div>

                <div class="panel-body">


                    </div>

                </div>

            </div>

                <div class='col-xs-12 col-sm-6'>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h2 class='panel-title pull-left'>Your Exchanges</h2>
                            <div class='clearfix'></div>
                        </div>

                        <div class="panel-body">

                            <form  action="" method="POST">
                                {{ csrf_field() }}
                                <input type='hidden' name='action' value='update_user_exchanges' />

                                @foreach($exchanges as $exchange)

                                    <h4>{{ $exchange['title'] }}</h4>

                                    <div class='form-group'>
                                        <label>API Key</label>
                                        <input type='text' class='form-control' name="api_key[{{ $exchange->id }}]"" value="{{ $exchange->pivot['api_key'] }}" />
                                    </div>

                                    <div class='form-group'>
                                        <label>API Secret</label>
                                        <input type='text' class='form-control' name="api_secret[{{ $exchange->id }}]" value="{{ $exchange->pivot['api_secret'] }}" />
                                    </div>

                                        <hr />

                                @endforeach

                                <input type='submit' class='btn btn-primary' value='Submit' />

                            </form>

                        </div>

                    </div>

                </div>

            </div>

</div>

@endsection