@extends('layouts.app')

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

          <h3>Schemes containing this coin</h3>
@foreach($coin->schemes as $scheme)

  <div class='col-xs-6 col-sm-4'>
      <div class='well'>
    <h4> {{ $scheme->title }}</h4>
   <ul>
          <li>Buy Point: {{ $scheme->pivot->set_price }} </li>
          <li>Highest Price: {{ $scheme->pivot->highest_price }}</li>
          <li>Current Price: {{ $coin->latestCoinprice->current_price }}</li>
          <li>Been Bought? {{ $scheme->pivot->been_bought }}  </li>
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

        </div>
      </div>

    @endforeach

<a class="btn btn-info btn-sm" role="button" data-toggle="collapse" href="#collapseTable" aria-expanded="false" aria-controls="collapseExample">
  Show Price Table
</a>
<div class='collapse' id='collapseTable'>
					<table class='table table-bordered'>

						<tr><th>Date/Time</th><th>Price</th></tr>

						@foreach($coin->coinprices as $price) 

							<tr><td>{{ $price->created_at->toDayDateTimeString() }}</td><td>&euro;{{ $price->getFormattedPrice() }}</td></tr>

						@endforeach

					</table>
</div>
				</div>


			</div>
		</div>
	</div>
</div>


@endsection

@section('footer_scripts')

<script src="{{ asset('js/custom-coins.js') }}"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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

          	,['{{ $date }}', {{ $price }}]
          	
          	@endforeach
        ]);

        // Set chart options
        chart_options = {'title':'Live BTC Prices',
                       'width':'90%',
                       height: 400,
                       interpolateNulls: true,
           hAxis: { showTextEvery: Math.round(num_results/5) }
                   };

        // Instantiate and draw our chart, passing in some options.
        chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(chart_data, chart_options);
      }


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

    </script>

  @endsection