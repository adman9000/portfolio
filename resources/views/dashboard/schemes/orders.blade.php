@extends('layouts.dashboard')

@section('content')


	<div class="container" ng-app='myApp' ng-controller='myCtrl'>

	    <div class="row">
	        <div class="col-md-10 col-md-offset-1">
	            <div class="panel panel-default">

					<table class='table table-bordered'>
						<thead><tr><th>Order Date</th><th>Sold</th><th>Bought</th><th>Status</th><th width=200></th></tr></thead>
						<tbody>

							@foreach ($scheme->transactions as $transaction)

									<tr class="{{ $transaction->_order ? 'bg-success' : 'bg-warning' }}">
									<td> {{ $transaction->created_at }} </td> 
									<td > {{ $transaction->amount_sold }} {{ isset($transaction->coinSold) ? $transaction->coinSold->code : "BTC" }} </td> 
									<td > {{ $transaction->amount_bought }}  {{ isset($transaction->coinBought) ?  $transaction->coinBought->code  : "BTC" }} </td> 
									<td > {{ $transaction->status }} </td> 
									<td align=right> 
									
									</td></tr>

							@endforeach

						</tbody>
					</table>

				</div>
			</div>
		</div>
	</div>

@endsection

@section('footer_scripts')


@endsection