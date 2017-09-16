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

          <ul>
          <li>Buy Point: {{ $coin->buy_point }} </li>
          <li>Been Bought? {{ $coin->been_bought }}  </li>
          <li>Sale 1 complete?  {{ $coin->sale_completed_1 }} </li>
          <li>Sale 2 Triggered?  {{ $coin->sale_trigger_2 }} </li>
          <li> Highest Price: {{ $coin->highest_price }}</li>
          </ul>

					<div id="chart_div"></div>

<a class="btn btn-primary" role="button" data-toggle="collapse" href="#collapseTable" aria-expanded="false" aria-controls="collapseExample">
  Show Table
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
    var num_results= <?=sizeof($coin->coinprices)?>;

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

			@foreach($coin->coinprices as $price) 

          	,['{{$price->created_at->format('D G:i')}}', {{$price->current_price}}]
          	
          	@endforeach
        ]);

        // Set chart options
        chart_options = {'title':'Live BTC Prices',
                       'width':'90%',
                       height: 400,
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