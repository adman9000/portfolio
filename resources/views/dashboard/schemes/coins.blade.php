@extends('layouts.dashboard')

@section('content') 

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

            	<form method='post' action='/schemes/{{ $scheme->id }}/coins'>

	            	{{csrf_field()}}

            		{{ method_field('PATCH') }}

	                <div class="panel-heading"><h3>{{ $scheme->title }}</h3></div>

	                <div class="panel-body">

						<p>Select which coins are included in this scheme and set a baseline BTC price for each (defaults to current price on bittrex)</p>

						<table class='table table-bordered table-condensed'>
							<thead><tr><th>Code</th><th>Name</th><th>Include?</th><th>Baseline Price</th></tr></thead>

							<tbody>

								@foreach($coins as $coin)

									<tr>
										<td>{{ $coin->code }}</td>
										<td>{{ $coin->name }}</td>
										<td><input type='checkbox' name='coins_included[]' value='{{ $coin->id }}' {{ $coin->is_included ? "Checked" : "" }} /></td>
										<td><input type='text' class='form-control' name='baseline_price[{{ $coin->id}}]' value='{{ $coin->baseline_price }} ' /></td>
									</tr>

								@endforeach

							</tbody>

						</table>


						<input type='submit' class='btn btn-primary' value='Submit' />

					</div>
				</form>

			</div>
		</div>
	</div>
</div>

@endsection