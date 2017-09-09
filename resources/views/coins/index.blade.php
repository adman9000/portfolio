@extends('layouts.app')

@section('content')


	<div class="container">
	    <div class="row">
	        <div class="col-md-8 col-md-offset-2">
	            <div class="panel panel-default">

					<table class='table table-bordered table-striped'>
						<thead><tr><th>Code</th><th>Name</th><th width=200></th></tr></thead>
						<tbody>

							@foreach ($coins as $coin)

									<tr><td> {{ $coin->code }} </td> <td > {{ $coin->name }} </td> <td align=right> <a class='btn btn-info btn-sm' href='/coins/{{ $coin->id }}'>View</a> <a class='btn btn-info btn-sm' href='/coins/{{ $coin->id }}/edit'>Edit</a>
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