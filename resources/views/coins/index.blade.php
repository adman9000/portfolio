@extends('layouts.app')

@section('content')


	<div class="container" ng-app='myApp' ng-controller='myCtrl'>


	    <div class="row">
	        <div class="col-md-10 col-md-offset-1">
	            <div class="panel panel-default">

					<table class='table table-bordered table-striped'>
						<thead><tr><th>Code</th><th>Name</th><th>Current Price</th><th>Amount Owned</th><th>Current Value<th width=200></th></tr></thead>
						<tbody>

							@foreach ($coins as $coin)

									<tr>
									<td> {{ $coin->code }} </td> 
									<td > {{ $coin->name }} </td> 
									<td ng-bind='current_price_{{ $coin->code }}'></td> 
									<td ng-bind='amount_owned_{{ $coin->code }}'></td>
									<td ng-bind='current_value_{{ $coin->code }}'></td>
									<td align=right> <a class='btn btn-info btn-sm' href='/coins/{{ $coin->id }}'>View</a> <a class='btn btn-info btn-sm' href='/coins/{{ $coin->id }}/edit'>Edit</a>
									<form method='post' action='/coins/{{$coin->id}}' class='pull-right' style='margin-left:5px;'>
										{{ csrf_field() }}
										{{ method_field('DELETE') }} 
										<input type='submit' class='btn btn-danger btn-sm' value='Delete' />
										</form>
									</td></tr>

							@endforeach

						</tbody>
					</table>

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

		console.log("{{$coin->code}}");
		console.log(parseFloat($scope.current_price_{{$coin->code}}));
		console.log(parseFloat($scope.amount_owned_{{$coin->code}}));


		$scope.current_value_{{$coin->code}} = parseFloat($scope.current_price_{{$coin->code}}) * parseFloat($scope.amount_owned_{{$coin->code}});
		
	@endforeach

	Pusher.subscribe('kraken', 'App\\Events\\PusherEvent', function (item) {
	data = angular.fromJson(item);
	console.log(data.message);
	message = angular.fromJson(data.message);
	angular.forEach(message, function(val, key) {
		console.log(key);
		console.log(val);
		price = val.price;
		eval("self.current_price_"+key+" = '"+price+"'");
		console.log("self.current_price_"+key+" = '"+price+"'");

		eval("self.current_value_"+key+" = parseFloat(self.current_price_"+key+") * parseFloat(self.amount_owned_"+key+")");
		console.log("self.current_value_"+key+" = parseFloat(self.current_price_"+key+") * parseFloat(self.amount_owned_"+key+")");
		
    })
  });


});
</script>

@endsection