@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                   
                   <div class='row'>

                        <div class='col-xs-6'>

                            <div class='well'>

                                <h4>Portfolio Pie Chart</h4>

                            </div>

                        </div>

                        <div class='col-xs-6'>

                            <div class='well'>

                                <h4>Portfolio Bar Chart</h4>

                            </div>

                        </div>

                        <div class='col-xs-6'>

                            <div class='well'>

                                <h4>Portfolio Gains Line Chart</h4>

                            </div>

                        </div>

                        <div class='col-xs-6'>

                            <div class='well'>

                                <h4>Top 5 Coins Table</h4>

                            </div>

                        </div>
                    </div>

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

  


                </div>
            </div>
        </div>
    </div>
</div>
@endsection
