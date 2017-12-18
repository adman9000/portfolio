@extends('layouts.dashboard')

@section('content')


	<div class="container" ng-app='myApp' ng-controller='myCtrl'>

	    <div class="row">
	        <div class="col-md-10 col-md-offset-1">
	            <div class="panel panel-default">

					<table class='table table-bordered'>
						<thead><tr><th>Code</th><th>Alert When</th><th>Current GBP Price</th><th>Current GBP Value</th><th width=200></th></tr></thead>
						<tbody>

							<?
							$btc_total = 0;
							$gbp_total = 0;
							?>

							@foreach ($alerts as $alert)

									<tr class="{{ $alert->triggered ? 'bg-success' : 'bg-warning'}}">
									<td > {{ $alert->coin->code }} </td> 
									<td > <?=$alert->text("<br />")?> </td> 
									<td > &pound;{{ $alert->currentPrice() }} </td> 
									<td > &pound;{{ $alert->currentValue() }} </td> 
									<td align=right> 
									<a class='btn btn-info btn-sm' href='{{ route('dashboard') }}/alerts/{{ $alert->id }}'>View</a> 
									<a class='btn btn-info btn-sm' href='{{ route('dashboard') }}/alerts/{{ $alert->id }}/edit'>Edit</a>
									<form method='post' action='{{ route('dashboard') }}/alerts/{{$alert->id}}' class='pull-right' style='margin-left:5px;'>
										{{ csrf_field() }}
										{{ method_field('DELETE') }} 
										<input type='submit' class='btn btn-danger btn-sm' value='Delete' />
										</form>
									</td></tr>

							@endforeach

						</tbody>

					</table>

					<br />
					<a href='{{ route('dashboard') }}/alerts/create' class='btn btn-info'>Add Alert</a>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('footer_scripts')


@endsection