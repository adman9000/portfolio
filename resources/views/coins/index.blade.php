@extends('layouts.app')

@section('content')


	<div class="container" ng-app='myApp' ng-controller='myCtrl'>


	    <div class="row">
	        <div class="col-md-12">
	            <div class="panel panel-default">

					<table class='table table-bordered table-striped table-condensed'>
						<thead><tr><th></th><th>Code</th><th>Name</th><th>Buy price</th><th>BTC Price</th><th>+ / -</th><?php /*<th>Amount Owned</th>*/ ?><th>BTC Value</th><th width=200></th></tr></thead>
						<tbody>

							@foreach ($coins as $i=>$coin)


									<tr><td ng-class='class_{{ $coin->code }}'><?=$i+1?></td>
									<td> {{ $coin->code }} </td> 
									<td > {{ $coin->name }} </td> 
									<td >{{ $coin->buy_point }}</td>
									<td ng-bind='current_price_{{ $coin->code }}'></td> 
									<td ng-bind='current_diff_{{ $coin->code }}' ng-class='current_diff_class_{{ $coin->code }}'></td> 
									<?php /*<td ng-bind='amount_owned_{{ $coin->code }}'></td>*/ ?>
									<td ng-bind='current_value_{{ $coin->code }}'></td> 
									
									<td align=right> <a class='btn btn-info btn-xs' href='/coins/{{ $coin->id }}'>View</a> <a class='btn btn-info btn-xs' href='/coins/{{ $coin->id }}/edit'>Edit</a>
									<form method='post' action='/coins/{{$coin->id}}' class='pull-right' style='margin-left:5px;'>
										{{ csrf_field() }}
										{{ method_field('DELETE') }} 
										<input type='submit' class='btn btn-danger btn-xs' value='X' />
										</form>
									</td></tr>


							@endforeach

						</tbody>

						<tfoot><tr><th></th><th></th><th></th><th></th><th></th><?php /*<th></th>*/?><th></th><th ng-bind='current_total_xbt'></th><th></th></tr></tfoot>
					</table>

					<p>Additional BTC held: <b><span ng-bind='amount_owned_XBT'></span></b></p>
					<p>Total BTC: <b><span ng-bind='total_XBT'></span></b></p>
					<p>BTC/USD Rate: <b><span ng-bind='xbt_rate'></span></b></p>
					<p>Total USD value: <b>$<span ng-bind='current_total_usd'></span></b></p>
					<p>USD/GBP Rate: <b><span ng-bind='usd_gbp_rate'></span></b></p>
					<p>Total GBP value: <b>£<span ng-bind='current_total_gbp'></span></b></p>

<hr />
<p>Starting BTC amount: 0.39515752</p>
<p>Approx starting GBP Value: £1071.15</p>
					<br />
					<a href='/coins/create' class='btn btn-info'>Add Coin</a>
					<br />
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
	
	$scope.xbt_rate = {{ $btc_usd_rate }};
	$scope.usd_gbp_rate = {{ $usd_gbp_rate }};

	$scope.amount_owned_XBT = {{ $btc_additional_amount }};

	  var current_price_class_XBT = "text-danger";

	//Initial setting  
	@foreach ($coins as $coin)


		@if($coin->latestCoinPrice)

			$scope.current_price_{{$coin->code}} = {{ $coin->latestCoinPrice->current_price }};

		@else

			$scope.current_price_{{$coin->code}} = "0";

		@endif

		@if($coin->amount_owned) 

			$scope.amount_owned_{{$coin->code}} = {{ $coin->amount_owned }};

		@else

			$scope.amount_owned_{{$coin->code}} = "0";

		@endif

		//console.log("{{$coin->code}}");
		//console.log(parseFloat($scope.current_price_{{$coin->code}}));
		//console.log(parseFloat($scope.amount_owned_{{$coin->code}}));


		current_value  = parseFloat($scope.current_price_{{$coin->code}}) * parseFloat($scope.amount_owned_{{$coin->code}});

		//Calculate percent diff between buy price & current price
		diff = ($scope.current_price_{{$coin->code}} / {{ $coin->buy_point }} * 100) - 100;

		$scope.current_diff_{{$coin->code}} = diff.toFixed(2)+"%";

		//set diff class
		if(diff<0) $scope.current_diff_class_{{$coin->code}} = "text-danger";
		else if(diff>0) $scope.current_diff_class_{{$coin->code}} = "text-success";

		$scope.current_price_{{$coin->code}} = parseFloat($scope.current_price_{{$coin->code}}).toFixed(7);
		$scope.amount_owned_{{$coin->code}} = parseFloat($scope.amount_owned_{{$coin->code}}).toFixed(7);
		
		$scope.current_value_{{$coin->code}} = current_value.toFixed(7);

		$scope.euro_value_{{$coin->code}} = current_value * $scope.xbt_rate;

		


		current_total += current_value;

		//Set TR classes
		if({{ $coin->sale_completed_1 }})
			$scope.class_{{$coin->code}}='bg-warning';
		else if({{ $coin->been_bought }})
		 	$scope.class_{{$coin->code}}='bg-success';
		 else
		 	$scope.class_{{$coin->code}}='bg-danger';


	@endforeach

	$scope.current_total_xbt = current_total.toFixed(7);

	$scope.total_XBT = ({{ $btc_additional_amount }} + current_total).toFixed(7);

    $scope.current_total_usd = $scope.total_XBT * {{ $btc_usd_rate }};

    $scope.current_total_gbp = $scope.current_total_usd / $scope.usd_gbp_rate ;

    //Deal with pusher events
	Pusher.subscribe('kraken', 'portfolio\\prices', function (item) {
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
			//var old_price = eval("self.current_price_"+key);

			eval("self.current_price_"+key+" = "+price+".toFixed(7)");
			//console.log("self.current_price_"+key+" = '"+price+"'");

			eval("current_value = parseFloat(price) * parseFloat(self.amount_owned_"+key+")");
			//console.log("self.current_value_"+key+" = parseFloat(self.current_price_"+key+") * parseFloat(self.amount_owned_"+key+")");
			
			eval("self.current_value_"+key+" = current_value.toFixed(7)");

			current_total += current_value;

			//Calculate percent diff between buy price & current price
			diff = val.diff;

			eval("$scope.current_diff_"+key+" = diff.toFixed(2)+'%'");

			//set diff class
			if(diff<0) eval("$scope.current_diff_class_"+key+" = 'text-danger'");
			else if(diff>0) eval("$scope.current_diff_class_"+key+" = 'text-success'");

	    })

	    //$scope.current_total = current_total;
	    $scope.current_total_xbt = current_total.toFixed(7);
	    $scope.total_XBT = ($scope.amount_owned_XBT + current_total).toFixed(7);
	    $scope.current_total_usd =  (($scope.amount_owned_XBT + current_total)*self.xbt_rate).toFixed(2);

	    $scope.current_total_gbp = ((($scope.amount_owned_XBT + current_total)*self.xbt_rate) / $scope.usd_gbp_rate).toFixed(2) ;

	    $scope.last_updated = "Last Update: " + dt;
	});


	Pusher.subscribe('kraken', 'portfolio\\trades', function (item) {
		data = angular.fromJson(item);
		//console.log(data.message);
		message = angular.fromJson(data.message);
		var current_total = 0;
		var dt;

		console.log(message);

		coins = angular.fromJson(message.coins);
		angular.forEach(coins, function(val, key) {

			eval("self.amount_owned_"+key+" = '"+val.amount_owned+"'");
			
			console.log("self.amount_owned_"+key+" = '"+val.amount_owned+"'");

			if(val.sale_completed_1)
				eval("self.class_"+key+"='bg-warning'");
			else if(val.been_bought)
			 	eval("self.class_"+key+"='bg-success'");
			 else
			 	eval("self.class_"+key+"='bg-danger'");
	    })

	    $scope.amount_owned_XBT = message.btc_additional_amount;

	    console.log(message.sales);
	    console.log(message.buys);
	});

});
</script>

@endsection