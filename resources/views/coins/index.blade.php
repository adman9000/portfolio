@extends('layouts.app')

@section('content')


	<div class="container" ng-app='myApp' ng-controller='myCtrl'>


	    <div class="row">
	        <div class="col-md-12">
	            <div class="panel panel-default">

<p>Remove bought at & Diff once initial coins are sold</p>

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

						<th><a href="#" ng-click="sortType = 'balance';  sortType == 'balance' ? sortReverse = !sortReverse : ''">Balance 
								<span ng-show="sortType == 'balance' && !sortReverse" class="glyphicon glyphicon-sort-by-attributes"></span>
								<span ng-show="sortType == 'balance' && sortReverse" class="glyphicon glyphicon-sort-by-attributes-alt"></span>
							</span></a></th>
						
						<th><a href="#" ng-click="sortType = 'buy_point'; sortReverse = !sortReverse">Bought At 
								<span ng-show="sortType == 'buy_point' && !sortReverse" class="glyphicon glyphicon-sort-by-attributes"></span>
								<span ng-show="sortType == 'buy_point' && sortReverse" class="glyphicon glyphicon-sort-by-attributes-alt"></span>
						</a></th>

						<th><a href="#" ng-click="sortType = 'current_price'; sortReverse = !sortReverse">BTC Price 
								<span ng-show="sortType == 'current_price' && !sortReverse" class="glyphicon glyphicon-sort-by-attributes"></span>
								<span ng-show="sortType == 'current_price' && sortReverse" class="glyphicon glyphicon-sort-by-attributes-alt"></span>
						</a></th>

						<th><a href="#" ng-click="sortType = 'diff'; sortReverse = !sortReverse">Diff
								<span ng-show="sortType == 'diff' && !sortReverse" class="glyphicon glyphicon-sort-by-attributes"></span>
								<span ng-show="sortType == 'diff' && sortReverse" class="glyphicon glyphicon-sort-by-attributes-alt"></span>
						</a></th>

						
						<th><a href="#" ng-click="sortType = 'btc_value'; sortReverse = !sortReverse">BTC Value 
								<span ng-show="sortType == 'btc_value' && !sortReverse" class="glyphicon glyphicon-sort-by-attributes"></span>
								<span ng-show="sortType == 'btc_value' && sortReverse" class="glyphicon glyphicon-sort-by-attributes-alt"></span>
						</a></th>

						<th width=200></th></tr></thead>
						<tbody>

							<tr ng-repeat="coin in coins | orderBy:sortType:sortReverse | filter:searchCoins">
								<td>[[ coin.i ]]</td>
									<td >[[ coin.code ]] </td> 
									<td >[[ coin.name ]] </td> 
									<td >[[ coin.balance ]] </td> 
									<td >[[ coin.buy_point ]] </td> 
									<td >[[ coin.current_price]]</td> 
									<td >[[ coin.diff]]</td> 
									<td >[[ coin.btc_value]]</td> 
									
									<td align=right> 
										<a href='#[[ coin.id ]]' class='btn btn-danger btn-xs convert-btc'>Sell for BTC</a> 
									</td></tr>

						</tbody>

						<tfoot><tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th>{{ $btc_value }}</th><th></th></tr></tfoot>

					</table>

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

	$("body").on("click", ".convert-btc", function(e) {
		e.preventDefault();
		var id = $(this).attr("href").replace("#", "");
		$.ajax( { 
			url : "/coins/tobtc/"+id, 
			method : "GET",
			success : function(data) {
				console.log(data);
			}
		});
	});


app.controller('myCtrl', function($scope, $http) {

	$scope.sortType     = 'i'; // set the default sort type
	$scope.sortReverse  = false;  // set the default sort order
	$scope.searchCoins   = '';     // set the default search/filter term

	//Initial setting  
	var i=0;
	$scope.coins = [
		@foreach($coins as $c=>$coin)
			{i: {{$c+1}}, id : "{{$coin->id}}", code : "{{$coin->code}}", name : "{{$coin->name}}", balance : "{{$coin->balance}}",buy_point : "{{$coin->buy_point}}",current_price : "{{$coin->latestCoinprice->current_price}}", diff : "{{$coin->diff}}", btc_value : "{{ $coin->btc_value }}"},
		@endforeach
		];
	});
</script>
@endsection