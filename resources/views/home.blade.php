@extends('layouts.app')

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

                   


                    You are logged in!
                    <br />
                    <hr /><br />

                    <table class='table table-bordered'>
                        <tr><th>Total value in BTC of all coins on bittrex</th><td>{{ $btc_value }}</td></tr>
                        <tr><th>Total value in USD of all coins on bittrex</th><td>${{ $usd_value }}</td></tr>
                        <tr><th>Total value in GBP of all coins on bittrex</th><td>£{{ $gbp_value }}</td></tr>
                        <tr><th>Number of different coins owned</th><td>{{ $num_coins }}</td></tr>
                    </table>

                    <p><b>TODO:</b></p>

                    <ul>
                        <li>Move /schemes/edit to /schemes/coins and create proper edit scheme page</li>
                        <li>Modal view for coins on /schemes/1 to see buy/sell prices</li>
                        <li>Coin view chart to do 24hr/1 week/1 month/1 year</li>
                        <li>Chart to have zero baseline, show % changes for better view of swing sizes</li>
                        <li>Add coins to the 2 main schemes</li>
                        <li>Ensure transactions are recorded against schemes & orders pages work</li>
                        <li>Tidy up scheme view footer. Clearer comparison of invested vs current value</li>
                    </ul>

                     @else

                        
                        <p><b>AutoTrader Site</b></p>
                    <p>Set up multiple schemes to run on bittrex. Monitor progress, cancel or modify schemes at any time. Place additional orders without affecting schemes or add/remove coins from schemes.</p>

                    <p>Each scheme allows you to set a baseline price for each coin (defaults to current price). You then set a % drop at which to buy the coin and 2 sell points in order to stage sell</p> 

                    <p><b>NOT YET READY FOR PUBLIC USE</b></p>

                        <a href="{{ url('/login') }}">Login</a> or <a href="{{ url('/register') }}">Register</a>
                                
                    @endif



                </div>
            </div>
        </div>
    </div>
</div>
@endsection
