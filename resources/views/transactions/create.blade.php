@extends('layouts.app')

@section('content') 

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

            	<form method='post' action='/transactions'>

            	{{csrf_field()}}

                <div class="panel-heading">Record a Transaction</div>

                <div class="panel-body">

					 <div class='form-group'>
						<label>Coin Sold</label>
						
						<select name='coin_sold_id' class='form-control' required>

							@foreach($coins as $coin)

								<option value='{{ $coin->id }}' > {{ $coin->name }} </option>

							@endforeach

						</select>

					</div>

					 <div class='form-group'>
						<label>Coin Bought</label>
						
						<select name='coin_bought_id' class='form-control' required>

							@foreach($coins as $coin)

								<option value='{{ $coin->id }}' > {{ $coin->name }} </option>

							@endforeach

						</select>
						
					</div>

					 <div class='form-group'>
						<label>Amount Sold</label>
						<input type='text' name='amount_sold' class='form-control' />
					</div>

					 <div class='form-group'>
						<label>Amount Bought</label>
						<input type='text' name='amount_bought' class='form-control' />
					</div>


					 <div class='form-group'>
						<label>Exchange Rate</label>
						<input type='text' name='exchange_rate' class='form-control' />
					</div>


					 <div class='form-group'>
						<label>Fees</label>
						<input type='text' name='fees' class='form-control' />
					</div>

					<div class='form-group'>
						<input type='submit' value='Submit' class='btn btn-primary' />
					</div>

				</div>

				</form>

			</div>
		</div>
	</div>
</div>

@endsection