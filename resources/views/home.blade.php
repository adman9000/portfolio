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

                   <p>This page uses live data from bittrex for market values and coin balances. The coin list & scheme pages use data stored in the database & updated every 5 minutes from Bittrex.</p>
                    <br />
                    <hr /><br />

                    <ul>
                        <li>BTC VALUE: {{ $btc_value }}</li>
                        <li>USD VALUE: ${{ $usd_value }}</li>
                        <li>GBP VALUE: £{{ $gbp_value }}</li>
                    </ul>

                    <p><b>TRADING ASSISTANT TODO:</b></p>

                    <ul>
                        <li>Exchanges class to then call bittrex, binance, kraken classes which return standardised data using APIS</li>
                        <li>Show overview on homepage - Total BTC value on each exchange, approx GBP value, plus coins</li>
                        <li>Quick links for buying & selling</li>
                        <li>Email alerts</li>
                    </ul>

                    <p><b>AUTOTRADER TODO:</b></p>

                    <ul>
                        <li class='text-success'>Move /schemes/edit to /schemes/coins and create proper edit scheme page</li>
                        <li>Modal view for coins on /schemes/1 to see buy/sell prices</li>
                        <li>Coin view chart to do 24hr/1 week/1 month/1 year</li>
                        <li class='text-success'>Chart to have zero baseline, show % changes for better view of swing sizes</li>
                        <li class='text-success'>Add coins to the 2 main schemes</li>
                        <li>Ensure transactions are recorded against schemes & orders pages work</li>
                        <li>Tidy up scheme view footer. Clearer comparison of invested vs current value</li>
                        <li>Add a chart to scheme view - BTC invested vs BTC value over time</li>
                        <li>Market price buy/sell to make sure it gets it? utilise order statuses to check amount bought/sold & rate</li>
                        <li>Notifications when attempting to buy or sell - email/slack/windows</li>
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
