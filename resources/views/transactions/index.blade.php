@extends('layouts.app')

@section('content')


	<div class="container" ng-app='myApp' ng-controller='myCtrl'>

	    <div class="row">
	        <div class="col-md-10 col-md-offset-1">
	            <div class="panel panel-default">

					<table class='table table-bordered table-striped'>
						<thead><tr><th>Coin Bought</th><th>Amount Bought</th><th>Bought At</th><th width=200></th></tr></thead>
						<tbody>

							@foreach ($transactions as $transaction)

									<tr>
									<td> {{ $transaction->coinBought->code }} </td> 
									<td > {{ $transaction->amount_bought }} </td> 
									<td > {{ $transaction->exchange_rate }} </td> 
									<td align=right> 
									<a class='btn btn-info btn-sm' href='/transactions/{{ $transaction->id }}'>View</a> 
									<a class='btn btn-info btn-sm' href='/transactions/{{ $transaction->id }}/edit'>Edit</a>
									<form method='post' action='/transactions/{{$transaction->id}}' class='pull-right' style='margin-left:5px;'>
										{{ csrf_field() }}
										{{ method_field('DELETE') }} 
										<input type='submit' class='btn btn-danger btn-sm' value='Delete' />
										</form>
									</td></tr>

							@endforeach

						</tbody>
					</table>

					<br />
					<a href='/transactions/create' class='btn btn-info'>Add Transaction</a>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('footer_scripts')


@endsection