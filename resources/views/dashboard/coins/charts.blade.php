@extends('layouts.dashboard')

@section('content') 

<div class="container" >

@foreach($coins as $coin)

    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">

    

                <div class="panel-heading">{{ $coin->name }} ( {{ $coin->code }} )</div>

                <div class="panel-body">


      					 <div id="chart_div_{{ $coin->id}}"></div>

      				</div>


			</div>
		</div>
	</div>

  @endforeach

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
      google.charts.setOnLoadCallback(drawCharts);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawCharts() {

        
        @foreach($coins as $coin) 

          // Create the data table.
          chart_data = new google.visualization.arrayToDataTable([
          	['Date/Time','Price']

  			   @foreach($coin->coinprices as $price) 

            	,['{{$price->created_at->format('D G:i')}}', {{$price->current_price}}]
            	
            	@endforeach
          ]);

          // Set chart options
          chart_options = {'title':'Live Prices',
                         'width':'90%',
                         height: 400,
             hAxis: { showTextEvery: Math.round(num_results/5) }
                     };

          // Instantiate and draw our chart, passing in some options.
          chart = new google.visualization.LineChart(document.getElementById('chart_div_{{ $coin->id}}'));
          chart.draw(chart_data, chart_options);

        @endforeach

      }



    </script>

  @endsection