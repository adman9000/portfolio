@extends('layouts.dashboard')

@section('content') 

<div class="container" >
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">

                <div class="panel-heading">View Coin</div>

                <div class="panel-body">

      					 <div class='form-group'>
      						<label>Coin Code</label>
      						{{ $coin->code }}
      					</div>

      					 <div class='form-group'>
      						<label>Coin Name</label>
      						{{ $coin->name }}
      					</div>

               

      					<div id="chart_div"></div>

                <h3>Exchanges</h3>
                @foreach($coin->exchanges as $exchange) 

                <div class='col-xs-3'>
                    <div class='well'>

                      <h4>{{ $exchange->title }}</h4>

                      <p>Coin Code: {{ $exchange->pivot->code }}</p>
                      <p>BTC Price: {{ $exchange->pivot->btc_price }}</p>
                      <p>USD Price: ${{ $exchange->pivot->usd_price }}</p>
                      <p>GBP Price: £{{ $exchange->pivot->gbp_price }}</p>

                    </div>
                  </div>

                @endforeach


                @can('autotrade')

                    <h3>Schemes containing this coin</h3>
                    @foreach($coin->schemes as $scheme)

                      <div class='col-sm-6 col-md-4'>
                          <div class='well'>
                        <h4> {{ $scheme->title }}</h4>
                       <ul>
                              <li>Baseline Price: {{ $scheme->pivot->set_price }} </li>
                              <li>Highest Price: {{ $scheme->pivot->highest_price }}</li>
                              <li>Current Price: {{ $coin->latestCoinprice->current_price }}</li>
                              <li>Buy Price: {{ $scheme->pivot->buy_price }} </li>
                              <li>Buy Amount: {{ $scheme->pivot->buy_amount }} </li>
                              <li>Been Bought? {{ $scheme->pivot->been_bought }}  </li>
                              <li>Amount Held {{ $scheme->pivot->amount_held }}  </li>
                              <li>Sell Trigger 1: {{ $scheme->pivot->sell_trigger_1 }}</li>
                              <li>Min Sell Price 1: {{ $scheme->pivot->sell_point_1 }}</li>
                              <li>Sell Trigger 2: {{ $scheme->pivot->sell_trigger_2 }}</li>
                              <li>Min Sell Price 2: {{ $scheme->pivot->sell_point_2 }}</li>
                              <li>Sale 1 Triggered?  {{ $scheme->pivot->sale_1_triggered }} </li>
                              <li>Sale 1 complete?  {{ $scheme->pivot->sale_1_completed }} </li>
                              <li>Sale 2 complete?  {{ $scheme->pivot->sale_2_completed }} </li>
                              <li>Sale 2 Triggered?  {{ $scheme->pivot->sale_2_triggered }} </li>
                              <li>Sale 1 Percent: {{ $scheme->sell_1_sell_percent }} </li>
                              <li>Sale 2 Percent: {{ $scheme->sell_2_sell_percent }} </li>
                              </ul>
                              <p>
                                <a href='/schemes/{{ $scheme->id }}'>View Scheme</a>
                              </p>
                            </div>
                          </div>

                        @endforeach

                @endcan

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
    var num_results= <?=sizeof($chart_data)?>;

      // Load the Visualization API and the corechart package.
      google.charts.load('current', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        chart_data = new google.visualization.arrayToDataTable([
        	['Date/Time','Price']

			@foreach($chart_data as $date=>$price) 

          	,['{{ date("d M G:i", strtotime($date)) }}', {{ $price }}]
          	
          	@endforeach
        ]);

        // Set chart options
        chart_options = {'title':'CoinMarketCap BTC Prices',
                       'width':'90%',
                       height: 400,
                       interpolateNulls: true,
           hAxis: { showTextEvery: Math.round(num_results/5) },
                  
          vAxis: { baseline : 0 }
                   };

        // Instantiate and draw our chart, passing in some options.
        chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(chart_data, chart_options);
      }

/*
app.controller('myCtrl', function($scope, $http, Pusher) {

    Pusher.subscribe('kraken', 'App\\Events\\PusherEvent', function (item) {
		data = angular.fromJson(item);
		console.log(data.message);
		message = angular.fromJson(data.message);
		var dt = message['{{$coin->code}}'].updated_at_short;
		var pr = parseFloat(message['{{$coin->code}}'].price);

		console.log(dt+" : "+pr);

		var arr = [dt, pr];
		chart_data.addRow(arr);

		num_results++;
		chart_options.hAxis.showTextEvery = Math.round(num_results/5);
		chart.draw(chart_data, chart_options);

    $scope.last_updated = "Last Update: " + dt;
	  });
});
*/
    </script>

  @endsection