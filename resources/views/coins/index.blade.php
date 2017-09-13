@extends('layouts.app')

@section('content')


	<div class="container" ng-app='myApp' ng-controller='myCtrl'>


	    <div class="row">
	        <div class="col-md-10 col-md-offset-1">
	            <div class="panel panel-default">

					<table class='table table-bordered table-striped'>
						<thead><tr><th>Code</th><th>Name</th><th>Amount Owned</th><th>XBT Exchange Rate</th><th>XBT Value</th><th>Euro Value</th><th width=200></th></tr></thead>
						<tbody>

							@foreach ($coins as $coin)


									<tr>
									<td> {{ $coin->code }} </td> 
									<td > {{ $coin->name }} </td> 
									<td ng-bind='amount_owned_{{ $coin->code }}'></td>
									<td ng-bind='current_price_{{ $coin->code }}' ng-class='current_price_class_{{ $coin->code }}'></td> 
									<td ng-bind='current_value_{{ $coin->code }}' ng-class='current_value_class_{{ $coin->code }}'></td> 
									<td ng-bind='euro_value_{{ $coin->code }}' ng-class='euro_value_class_{{ $coin->code }}'></td> 
									<td align=right> <a class='btn btn-info btn-sm' href='/coins/{{ $coin->id }}'>View</a> <a class='btn btn-info btn-sm' href='/coins/{{ $coin->id }}/edit'>Edit</a>
									<form method='post' action='/coins/{{$coin->id}}' class='pull-right' style='margin-left:5px;'>
										{{ csrf_field() }}
										{{ method_field('DELETE') }} 
										<input type='submit' class='btn btn-danger btn-sm' value='Delete' />
										</form>
									</td></tr>


							@endforeach

						</tbody>

						<tfoot><tr><th></th><th></th><th></th><th></th><th ng-bind='current_total_xbt'></th><th ng-bind='current_total_euro'></th><th></th></tr></tfoot>
					</table>

					<p>Total XBT value: <b><span ng-bind='current_total_xbt'></span></b></p>
					<p>XBT/Euro Rate: <b><span ng-bind='xbt_rate'></span></b></p>
					<p>Total Euro value: <b><span ng-bind='current_total_euro'></span></b></p>

					<br />
					<a href='/coins/create' class='btn btn-info'>Add Coin</a>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('footer_scripts')

<script src="{{ asset('js/custom-coins.js') }}"></script>

<script>

app.controller('myCtrl', function($scope, $http, Pusher) {

	  var self = $scope;

	  var current_total = 0;
	  var current_value = 0;
	
		$scope.xbt_rate = {{$XBT->latestCoinPrice->current_price }};

	  var current_price_class_XBT = "text-danger";

	//Initial setting  
	@foreach ($coins as $coin)


		@if($coin->latestCoinPrice)

			$scope.current_price_{{$coin->code}} = "{{ $coin->latestCoinPrice->current_price }}";

		@else

			$scope.current_price_{{$coin->code}} = "0";

		@endif

		@if($coin->amount_owned) 

			$scope.amount_owned_{{$coin->code}} = "{{ $coin->amount_owned }}";

		@else

			$scope.amount_owned_{{$coin->code}} = "0";

		@endif

		//console.log("{{$coin->code}}");
		//console.log(parseFloat($scope.current_price_{{$coin->code}}));
		//console.log(parseFloat($scope.amount_owned_{{$coin->code}}));


		current_value  = parseFloat($scope.current_price_{{$coin->code}}) * parseFloat($scope.amount_owned_{{$coin->code}});
		
		$scope.current_value_{{$coin->code}} = current_value;

		$scope.euro_value_{{$coin->code}} = current_value * $scope.xbt_rate;

		current_total += current_value;


	@endforeach

	$scope.current_total_xbt = current_total;

    $scope.current_total_euro = current_total * {{ $XBT->latestCoinPrice->current_price }};

	Pusher.subscribe('kraken', 'App\\Events\\PusherEvent', function (item) {
	data = angular.fromJson(item);
	//console.log(data.message);
	message = angular.fromJson(data.message);
	var current_total = 0;
	var dt;
	angular.forEach(message, function(val, key) {
		//console.log(key);
		//console.log(val);
		price = val.price;
		dt = val.updated_at_short;

		//get old price
		var old_price = eval("self.current_price_"+key);

		eval("self.current_price_"+key+" = '"+price+"'");
		//console.log("self.current_price_"+key+" = '"+price+"'");

		eval("current_value = parseFloat(self.current_price_"+key+") * parseFloat(self.amount_owned_"+key+")");
		//console.log("self.current_value_"+key+" = parseFloat(self.current_price_"+key+") * parseFloat(self.amount_owned_"+key+")");
		
		eval("self.current_value_"+key+" = current_value");

		eval("self.euro_value_"+key+" = current_value*self.xbt_rate");


		//Set colour based on price change
		if(price > old_price)
	  		eval("self.current_price_class_"+key+" = 'text-success'");
	  	else if(price < old_price)
	  		eval("self.current_price_class_"+key+" = 'text-danger'");
	  	else 
	  		eval("self.current_price_class_"+key+" = 'text-default'");

	  	if(key != "XBT")
				current_total += current_value;
		else
			self.xbt_rate = price;

    })

    $scope.current_total = current_total;
    $scope.current_total_xbt = current_total;
    $scope.current_total_euro = current_total*self.xbt_rate;



    $scope.last_updated = "Last Update: " + dt;
  });


});
</script>

@endsection