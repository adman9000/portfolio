@extends('layouts.app')

@section('content') 

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

    

                <div class="panel-heading">View Coin</div>

                <div class="panel-body">

					 <div class='form-group'>
						<label>Coin Code</label>
						{{ $coin->code }}
					</div>

					 <div class='form-group'>
						<label>Coin Name</label>
						{{ $coin->name }}
					</div>


					<table class='table table-bordered'>

						<tr><th>Date/Time</th><th>Price</th></tr>

						@foreach($coin->coinprices as $price) 

							<tr><td>{{ $price->created_at->toDayDateTimeString() }}</td><td>&euro;{{ $price->getFormattedPrice() }}</td></tr>

						@endforeach

					</table>

				</div>


			</div>
		</div>
	</div>
</div>

@endsection