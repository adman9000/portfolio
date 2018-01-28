@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class='panel-title pull-left'>Dashboard</h3>
                     <!-- Right Side Of Navbar -->
                     <!-- Single button -->
                    <div class="btn-group pull-right">
                      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        View <span class="caret"></span>
                      </button>
                    <ul class="dropdown-menu">
                            <li><a href="{{ route('dashboard') }}">All</a></li>
                            <li><a href="{{ route('dashboard') }}?type=exchange">Exchanges</a></li>
                            <li><a href="{{ route('dashboard') }}?type=wallet">Wallets</a></li>
                    </ul>
                </div>
                    <div class='clearfix'></div>
                </div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                   
                   <div class='row'>

                        <div class='col-xs-12 col-sm-6'>

                            <div class='well'>

                                <h4>Portfolio Pie Chart</h4>

                                <div id='chart-1'></div>

                            </div>

                        </div>

                        <div class='col-xs-12 col-sm-6'>

                            <div class='well'>

                                <h4>Portfolio Bar Chart</h4>

                                <div id='chart-2'></div>

                            </div>

                        </div>

                        <div class='col-xs-12 col-sm-6'>

                            <div class='well'>

                                <h4 class='pull-left'>Portfolio Gains Line Chart</h4>
                                <div class="btn-group pull-right">
                                      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        View <span class="caret"></span>
                                      </button>
                                    <ul class="dropdown-menu">
                                            <li><a href="#" class='update-graph' data-period='24hour'>24 Hour</a></li>
                                            <li><a href="#" class='update-graph' data-period='7day' >7 Day</a></li>
                                            <li><a href="#" class='update-graph' data-period='1month' >1 Month</a></li>
                                    </ul>
                                </div>
                                <div class='clearfix'></div>

                                <div id='chart-3'></div>

                            </div>

                        </div>

                        <div class='col-xs-12 col-sm-6'>

                            <div class='well'>

                                <h4>Your Top 12 Coins</h4>

                                <div id='chart-4'>

                                    <table class='table table-bordered table-condensed'>
                                        <thead><tr><th>Code</th><th  class='hidden-xs hidden-sm'>Total Balance</th><th>GBP Value</th><th>1 hour</th><th>1 day</th><th>1 week</th></tr></thead>

                                        <tbody>
                                            <? $i=0;?>
                                            @foreach( $coins as $coin)
                                                <? if($i++>=12) break; ?>
                                                <tr>
                                                    <td>{{ $coin['code'] }}</td>
                                                    <td class='hidden-xs hidden-sm'>{{ $coin['balance'] }}</td>
                                                    <td>£{{ number_format($coin['gbp_value'], 2) }}</td>

                                                    <td @if ($coin['gbp_value_1_hour'] < $coin['gbp_value']) 
                                                        class='text-success' 
                                                        @else
                                                            class='text-danger' 
                                                        @endif
                                                    >£{{ number_format($coin['gbp_value'] - $coin['gbp_value_1_hour'], 2) }}</td>

                                                    <td @if ($coin['gbp_value_1_day'] < $coin['gbp_value']) 
                                                        class='text-success' 
                                                        @else
                                                            class='text-danger' 
                                                        @endif
                                                    >£{{ number_format($coin['gbp_value'] - $coin['gbp_value_1_day'], 2) }}</td>

                                                    <td @if ($coin['gbp_value_1_week'] < $coin['gbp_value']) 
                                                        class='text-success' 
                                                        @else
                                                            class='text-danger' 
                                                        @endif
                                                    >£{{ number_format($coin['gbp_value'] - $coin['gbp_value_1_week'], 2) }}</td>

                                                </tr>
                                            
                                            @endforeach

                                        </tbody>

                                    </table>


                                </div>

                            </div>

                        </div>
                    </div>

                    <br />
                    <hr /><br />

                    <h3 style='text-align:center;'>Current Portfolio Value</h3>

                    <div class='row' style='text-align:center;'>
                        <div class='col-sm-4' style='margin-bottom:15px;'>
                            <div class='btn btn-info'><h4>{{ $btc_value }} BTC</h4></div>
                        </div>
                        <div class='col-sm-4' style='margin-bottom:15px;'>
                            <div class='btn btn-info'><h4>${{ $usd_value }}</h4></div>
                        </div>
                        <div class='col-sm-4' style='margin-bottom:15px;'>
                            <div class='btn btn-info'><h4>£{{ $gbp_value }}</h4></div>
                        </div>
                    </div>

                    <br />
                    <hr /><br />

                    <form method='post' action="{{ route('dashboard') }}">
                          {{ csrf_field() }}
                        <input type='hidden' name='action' value='resync' />
                        <input type='submit' value='Resync Balances' class='btn btn-sm btn-primary' />
                    </form>

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


@section('footer_scripts')

    <script type="text/javascript">

    var chart_data = new Array();
    var chart;
    var chart_options;

      // Load the Visualization API and the corechart package.
      google.charts.load('current', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawCharts);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawCharts() {

        // Portfolio Pie Chart

        chart_data = new google.visualization.arrayToDataTable([
            ['Coin','Value']

            @foreach($coins as $coin) 

            ,['{{ $coin['code'] }}', {{ $coin['gbp_value'] }}]
            
            @endforeach
        ]);

        // Set chart options
        chart_options = {'title':'Portfolio value (GBP)',
                       'width':'90%',
                       height: 400
                   };

        // Instantiate and draw our chart, passing in some options.
        chart = new google.visualization.PieChart(document.getElementById('chart-1'));
        chart.draw(chart_data, chart_options);

        //Portfolio Bar Chart - TODO: Show amount invested vs current value?

         chart_data = new google.visualization.arrayToDataTable([
            ['Coin','Value']

            @foreach($coins as $coin) 

            ,['{{ $coin['code'] }}', {{ $coin['gbp_value'] }}]
            
            @endforeach
        ]);

        // Set chart options
        chart_options = {'title':'Portfolio value (GBP)',
                       'width':'90%',
                       height: 400
                   };

        // Instantiate and draw our chart, passing in some options.
        chart = new google.visualization.BarChart(document.getElementById('chart-2'));
        chart.draw(chart_data, chart_options);

        //Portfolio Gains Line Chart

        chart_data = new google.visualization.arrayToDataTable([
            ['Date','Value']

            @foreach($chart as $date=>$value) 

            <? if($value) { ?>,['{{ $date }}', {{ $value }}]<? } ?>
            
            @endforeach
        ]);

        // Set chart options
        chart_options = {'title':'24 Hour Portfolio value (GBP)',
                       'width':'90%',
                       height: 400,
                       interpolateNulls: true
                   };

        // Instantiate and draw our chart, passing in some options.
        chart = new google.visualization.LineChart(document.getElementById('chart-3'));
        chart.draw(chart_data, chart_options);


      }


      //update the line graph
      $("document").ready(function() {

        $(".update-graph").click(function(e) {
            e.preventDefault();

            var period = $(this).data("period");
            $.ajax( { url : "/dashboard/ajax/portfolio?type=<?=request('type')?>&period="+period, success : function(data) {

                data = $.parseJSON(data);
                data = data['chart'];
                console.log(data);

                var data_array = new Array();;

                data_array.push(['Date', 'Value']);

                $.each(data, function(index, val) {
                    console.log(val);
                    data_array.push([index, val]);

                })

                console.log(data_array);

                chart_data = new google.visualization.arrayToDataTable(data_array);

                // Set chart options
                chart_options = {'title':'24 Hour Portfolio value (GBP)',
                               'width':'90%',
                               height: 400,
                               interpolateNulls: true
                           };

                // Instantiate and draw our chart, passing in some options.
                chart = new google.visualization.LineChart(document.getElementById('chart-3'));
                chart.draw(chart_data, chart_options);


            }});

        });

      });

    </script>

@endsection