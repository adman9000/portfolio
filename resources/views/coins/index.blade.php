@extends('layouts.app')

@section('content')


	<div class="container" ng-app='myApp' ng-controller='myCtrl'>


	    <div class="row">
	        <div class="col-md-12">
	            <div class="panel panel-default">



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

						<th><a href="#" ng-click="sortType = 'buy_point'; sortReverse = !sortReverse">Buy Point 
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
									<td >[[ coin.buy_point ]]</td>
									<td >[[ coin.current_price]]</td> 
									<td class="[[coin.diff_class]]">[[coin.diff]]</td> 
									<td >[[coin.current_value]]</td> 
									
									<td align=right> 
									</td></tr>

						</tbody>

						<tfoot><tr><th></th><th></th><th></th><th></th><th></th><th></th><th ng-bind='current_total_xbt'></th><th></th></tr></tfoot>
					</table>

					<p>Additional BTC held: <b><span ng-bind='amount_owned_XBT'></span></b></p>
					<p>Total BTC: <b><span ng-bind='total_XBT'></span></b></p>
					<p>BTC/USD Rate: <b><span ng-bind='xbt_rate'></span></b></p>
					<p>Total USD value: <b><span ng-bind='current_total_usd | currency'></span></b></p>
					<p>USD/GBP Rate: <b><span ng-bind='usd_gbp_rate'></span></b></p>
					<p>Total GBP value: <b><span ng-bind="current_total_gbp | currency : '£'"></span></b></p>

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

	$scope.sortType     = 'i'; // set the default sort type
	$scope.sortReverse  = false;  // set the default sort order
	$scope.searchCoins   = '';     // set the default search/filter term


	//Initial setting  
	$scope.coins = [];
	$scope.current_total_xbt = 0;
	$scope.amount_owned_XBT = 0;
	$scope.xbt_rate = 0;


	 //Deal with pusher events
	Pusher.subscribe('kraken', 'portfolio\\prices', function (item) {
		data = angular.fromJson(item);
		//console.log(data.message);
		message = angular.fromJson(data.message);
		$scope.current_total_xbt = 0;
		var dt;

		$scope.coins = [];
		self = $scope;
		var i = 0;
		angular.forEach(message, function(val, key) {


            if(val.diff<0) diff_class="text-danger";
            else if(val.diff>0) diff_class="text-success";
            if(val.sale_completed_1) row_class = "bg-warning";
            else if(val.been_bought) row_class="bg-success";
            else row_class = "bg-danger";

			self.coins[i] = { i : i+1, code : val.code, name : val.name, id : val.id, current_price : val.current_price, buy_point : val.buy_point, diff : val.diff, current_value : val.current_value, row_class : row_class, diff_class : diff_class };
		
			self.current_total_xbt += val.current_value;

			//Calculations
			$scope.total_XBT = $scope.amount_owned_XBT + $scope.current_total_xbt;
			$scope.current_total_usd = $scope.total_XBT * $scope.xbt_rate;
			$scope.usd_gbp_rate = {{$usd_gbp_rate}};
			$scope.current_total_gbp = $scope.current_total_usd / $scope.usd_gbp_rate;

			i++;

		});


	});

	 //Deal with BTCpusher event
	Pusher.subscribe('kraken', 'portfolio\\btc', function (item) {
		data = angular.fromJson(item);
		//console.log(data.message);
		message = angular.fromJson(data.message);
		console.log(message);
		$scope.amount_owned_XBT = message.btc_additional_amount;
		$scope.xbt_rate = message.btc_usd_rate;
	});

	//Do an ajax call in order to trigger getting prices etc
	$http.get('/exchanges/coinpusher')

});
</script>

@endsection