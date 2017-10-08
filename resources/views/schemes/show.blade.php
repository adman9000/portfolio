@extends('layouts.app')

@section('content') 

<div class="container" ng-app='myApp' ng-controller='myCtrl'>


	    <div class="row">
	        <div class="col-md-12">
	            <div class="panel panel-default">


	            	<div class='panel-heading'><h3 class='panel-title'>{{ $scheme->title }} Coin List</h3></div>

	            	<div class='panel-body'>

	            		<p>Displays all coins currently included in this scheme, with current price, baseline price & BTC value using data downloaded from Bittrex every 5 minutes. Auto-updates.</p>

	            		<p>Buy {{ $scheme->buy_amount }} BTC value at {{ $scheme->buy_drop_percent }}% below baseline. <br />
	            			Sell {{ $scheme->sell_1_sell_percent }}% after gaining at least {{ $scheme->sell_1_gain_percent }}% and then dropping {{ $scheme->sell_1_drop_percent }}. <br />
	            			@if($scheme->sell_2_gain_percent)
	            				Sell {{ $scheme->sell_2_sell_percent }}% of the remainder after gaining at least {{ $scheme->sell_2_gain_percent }}% and then dropping {{ $scheme->sell_2_drop_percent }}. <br />
	            			@endif
	            			Increase the baseline price by {{ $scheme->price_increase_percent }}% and repeat.
	            		</p>

<div class='form-group'>
	<div class="input-group">
<div class="input-group-addon"><i class="fa fa-search"></i>
<input type="text" class="form-control" placeholder="Search Coins" ng-model="searchCoins">
</div></div></div>
					<table class='table table-bordered table-striped table-condensed'>
						<thead><tr><th>
							<a href="#" ng-click="sortType = 'i'; sortType == 'i' ? sortReverse = !sortReverse : ''">Num 
								<span ng-show="sortType == 'i' && !sortReverse" class="glyphicon glyphicon-sort-by-attributes"></span>
								<span ng-show="sortType == 'i' && sortReverse" class="glyphicon glyphicon-sort-by-attributes-alt"></span>
							</a>
						</th>

						<th><a href="#" ng-click="sortType = 'code';  sortType == 'code' ? sortReverse = !sortReverse : ''">Code 
								<span ng-show="sortType == 'code' && !sortReverse" class="glyphicon glyphicon-sort-by-attributes"></span>
								<span ng-show="sortType == 'code' && sortReverse" class="glyphicon glyphicon-sort-by-attributes-alt"></span>
							</span></a></th>

						<th><a href="#" ng-click="sortType = 'name';sortReverse = !sortReverse">Name 
								<span ng-show="sortType == 'name' && !sortReverse" class="glyphicon glyphicon-sort-by-attributes"></span>
								<span ng-show="sortType == 'name' && sortReverse" class="glyphicon glyphicon-sort-by-attributes-alt"></span>
						</a></th>

						<th><a href="#" ng-click="sortType = 'buy_point'; sortReverse = !sortReverse">Baseline Price 
								<span ng-show="sortType == 'buy_point' && !sortReverse" class="glyphicon glyphicon-sort-by-attributes"></span>
								<span ng-show="sortType == 'buy_point' && sortReverse" class="glyphicon glyphicon-sort-by-attributes-alt"></span>
						</a></th>

						<th><a href="#" ng-click="sortType = 'current_price'; sortReverse = !sortReverse">BTC Price 
								<span ng-show="sortType == 'current_price' && !sortReverse" class="glyphicon glyphicon-sort-by-attributes"></span>
								<span ng-show="sortType == 'current_price' && sortReverse" class="glyphicon glyphicon-sort-by-attributes-alt"></span>
						</a></th>

						<th><a href="#" ng-click="sortType = 'diff'; sortReverse = !sortReverse">+ / - 
								<span ng-show="sortType == 'diff' && !sortReverse" class="glyphicon glyphicon-sort-by-attributes"></span>
								<span ng-show="sortType == 'diff' && sortReverse" class="glyphicon glyphicon-sort-by-attributes-alt"></span>
						</a></th>

						<th><a href="#" ng-click="sortType = 'current_value'; sortReverse = !sortReverse">BTC Value 
								<span ng-show="sortType == 'current_value' && !sortReverse" class="glyphicon glyphicon-sort-by-attributes"></span>
								<span ng-show="sortType == 'current_value' && sortReverse" class="glyphicon glyphicon-sort-by-attributes-alt"></span>
						</a></th>

						<th width=200></th></tr></thead>
						<tbody>

							<tr ng-repeat="coin in coins | orderBy:sortType:sortReverse | filter:searchCoins">
								<td class="[[ coin.row_class ]]">[[ coin.i ]]</td>
									<td >[[ coin.code ]] </td> 
									<td >[[ coin.name ]] </td> 
									<td >[[ coin.set_price ]]</td>
									<td >[[ coin.current_price]]</td> 
									<td class="[[coin.diff_class]]">[[coin.diff]]</td> 
									<td >[[coin.current_value]]</td> 
									
									<td align=right>

										<a href='/coins/[[ coin.id ]]' class='btn btn-xs btn-info'>View</a>
										
									</td></tr>

						</tbody>

						<tfoot><tr><th></th><th></th><th></th><th></th><th></th><th></th><th ng-bind='current_total_xbt'></th><th></th></tr></tfoot>
					</table>

					<p>BTC/USD Rate: <b><span ng-bind='xbt_rate'></span></b></p>
					<p>Scheme USD value: <b><span ng-bind='current_total_usd | currency'></span></b></p>
					<p>USD/GBP Rate: <b><span ng-bind='usd_gbp_rate'></span></b></p>
					<p>Scheme GBP value: <b><span ng-bind="current_total_gbp | currency : '£'"></span></b></p>

<hr />
<p>BTC Invested: <b>{{ $btc_invested }}</b></p>

					<br />

					<form method='post' action='/schemes/{{ $scheme->id }}/enable'>

	            	{{csrf_field()}}

            		{{ method_field('PATCH') }}

						<div class='form-group'>
							<label>Enable/Disable Scheme</label>
							<select name='enabled' class='form-control'>
								<option value='0' {{ $scheme->enabled ? "" : "SELECTED" }} >Disabled</option>
								<option value='1' {{ $scheme->enabled ? "SELECTED" : "" }} >Enabled</option>
							</select>
						</div>

						<input type='submit' class='btn btn-primary' value='Submit' />

					</div>
				</form>

				</div>
			</div>
		</div>
		</div>
	</div>



@endsection

@section('footer_scripts')

<script src="{{ asset('js/custom-coins.js') }}"></script>

<script>
    $("document").ready(function() {

 		if (! ('Notification' in window)) {
              alert('Web Notification is not supported');
              return;
            }

            Notification.requestPermission().then(function(result) {
			  if (result === 'denied') {
			    console.log('Permission wasn\'t granted. Allow a retry.');
			    return;
			  }
			  if (result === 'default') {
			    console.log('The permission request was dismissed.');
			    return;
			  }
			  // Do something with the granted permission.
			});

        });
</script>

<script>


app.controller('myCtrl', function($scope, $http, Pusher, $filter) {

	$scope.sortType     = 'i'; // set the default sort type
	$scope.sortReverse  = false;  // set the default sort order
	$scope.searchCoins   = '';     // set the default search/filter term


	//Initial setting  
	$scope.coins = [];
	$scope.current_total_xbt = 0;
	$scope.amount_owned_XBT = 0;
	$scope.xbt_rate = 0;


	 //Deal with pusher events
	Pusher.subscribe('kraken', 'portfolio\\prices\\{{ $scheme->id }}', function (item) {

		var date = new Date();
        $scope.time = $filter('date')(new Date(), 'HH:mm'); 

  		if(window.Notification) var notify = new Notification("Bittrex data received at "+$scope.time);

		data = angular.fromJson(item);
		//console.log(data.message);
		message = angular.fromJson(data.message);
		$scope.current_total_xbt = 0;
		var dt;

		$scope.coins = []
		@foreach($scheme->coins as $coin)
			$scope.coins["{{$coin->code}}"] = new Array();
		@endforeach
		
		self = $scope;
		var i = 0;
		angular.forEach(message, function(val, key) {

			//Only use coins that are in the array
			if(self.coins[val.code]) {


	            if(val.diff<0) diff_class="text-danger";
	            else if(val.diff>0) diff_class="text-success";
	            else diff_class="text-warning";

	            if(val.sale_completed_1) row_class = "bg-warning";
	            else if(val.been_bought) row_class="bg-success";
	            else row_class = "bg-danger";

				self.coins[i] = { i : i+1, code : val.code, name : val.name, id : val.id, current_price : val.current_price, set_price : val.set_price, diff : val.diff, current_value : val.current_value, row_class : row_class, diff_class : diff_class };
			
				self.current_total_xbt += val.current_value;

				//Calculations
				$scope.total_XBT = $scope.amount_owned_XBT + $scope.current_total_xbt;
				$scope.current_total_usd = $scope.total_XBT * $scope.xbt_rate;
				$scope.usd_gbp_rate = {{$usd_gbp_rate}};
				$scope.current_total_gbp = $scope.current_total_usd / $scope.usd_gbp_rate;

				i++;
			}

		});


	});

	 //Deal with BTCpusher event
	Pusher.subscribe('kraken', 'portfolio\\btc', function (item) {
		data = angular.fromJson(item);
		//console.log(data.message);
		message = angular.fromJson(data.message);
		//$scope.amount_owned_XBT = message.btc_additional_amount;
		$scope.xbt_rate = message.btc_usd_rate;
	});

	//Do an ajax call in order to trigger getting prices etc
	$http.get('/exchanges/coinpusher')

});
</script>


<?php /*
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

            	

	                <div class="panel-heading"><h3>{{ $scheme->title }}</h3></div>

	                <div class="panel-body">

					
						<table class='table table-bordered table-condensed'>
							<thead><tr><th>Code</th><th>Name</th><th>Include?</th><th>Baseline Price</th></tr></thead>

							<tbody>

								@foreach($scheme->coins as $coin)

									<tr>
										<td>{{ $coin->code }}</td>
										<td>{{ $coin->name }}</td>
										<td>{{ $coin->is_included ? "Yes" : "No" }}</td>
										<td>{{ $coin->pivot->set_price }}</td>
									</tr>

								@endforeach

							</tbody>

						</table>

	
			</div>
		</div>
	</div>
</div>
*/ ?>


@endsection