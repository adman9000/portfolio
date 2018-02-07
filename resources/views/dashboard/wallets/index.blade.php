@extends('layouts.dashboard')

@section('content')


	<div class="container" ng-app='myApp' ng-controller='myCtrl'>

	    <div class="row">
	        <div class="col-md-10 col-md-offset-1">
	            <div class="panel panel-default">

					<table class='table table-bordered table-striped'>
						<thead><tr><th>Code</th><th>Balance</th><th>BTC Value</th><th>GBP Value</th><th width=200></th></tr></thead>
						<tbody>

							<?
							$btc_total = 0;
							$gbp_total = 0;
							?>

							@foreach ($wallets as $wallet)
								<?
								$btc_total += $wallet->btc_value;
								$gbp_total += $wallet->gbp_value;
								?>
									<tr>
									<td > {{ $wallet->coin->code }} </td> 
									<td > {{ $wallet->balance }} </td> 
									<td > {{ $wallet->btc_value }} </td> 
									<td > &pound;{{ $wallet->gbp_value }} </td> 
									<td align=right> 
									<a class='btn btn-info btn-sm' href='{{ route('dashboard') }}/wallets/{{ $wallet->id }}'>View</a> 
									<a class='btn btn-info btn-sm' href='{{ route('dashboard') }}/wallets/{{ $wallet->id }}/edit'>Edit</a>
									<form method='post' action='{{ route('dashboard') }}/wallets/{{$wallet->id}}' class='pull-right' style='margin-left:5px;' onsubmit="return confirm('Are you sure?')">
										{{ csrf_field() }}
										{{ method_field('DELETE') }} 
										<input type='submit' class='btn btn-danger btn-sm' value='Delete' />
										</form>
									</td></tr>

							@endforeach

						</tbody>

						<tfoot>
							<tr><th></th><th></th>
								<th>{{ $btc_total }}</th>
								<th>&pound;{{ $gbp_total }}</th>
								<th></th>
							</tr>
						</tfoot>
					</table>

					<br />
					<a href='{{ route('dashboard') }}/wallets/create' class='btn btn-info'>Add Wallet</a>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('footer_scripts')


@endsection