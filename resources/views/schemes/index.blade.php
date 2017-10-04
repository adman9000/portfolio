@extends('layouts.app')

@section('content')


	<div class="container" ng-app='myApp' ng-controller='myCtrl'>

	    <div class="row">
	        <div class="col-md-10 col-md-offset-1">
	            <div class="panel panel-default">

					<table class='table table-bordered table-striped'>
						<thead><tr><th>Scheme Title</th><th>Date Started</th><th>Status</th><th width=200></th></tr></thead>
						<tbody>

							@foreach ($schemes as $scheme)

									<tr>
									<td> {{ $scheme->title }} </td> 
									<td > {{ $scheme->date_start }} </td> 
									<td > {{ $scheme->enabled ? "Enabled" : "Disabled"}} </td> 
									<td align=right> 
									<a class='btn btn-info btn-xs' href='/schemes/{{ $scheme->id }}'>View</a> 
									<a class='btn btn-info btn-xs' href='/schemes/{{ $scheme->id }}/orders'>Orders</a> 
									<a class='btn btn-info btn-xs' href='/schemes/{{ $scheme->id }}/edit'>Edit</a>
									<form method='post' action='/schemes/{{$scheme->id}}' class='pull-right' style='margin-left:5px;'>
										{{ csrf_field() }}
										{{ method_field('DELETE') }} 
										<input type='submit' class='btn btn-danger btn-xs' value='Delete' />
										</form>
									</td></tr>

							@endforeach

						</tbody>
					</table>

					<br />
					<a href='/schemes/create' class='btn btn-info'>Add Scheme</a>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('footer_scripts')


@endsection