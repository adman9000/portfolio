@extends('layouts.dashboard')

@section('content') 

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

            	<form method='post' action='{{ route('alerts') }}'>

            	{{csrf_field()}}

                <div class="panel-heading">Add an Alert</div>

                <div class="panel-body">

					 <div class='form-group'>
						<label>Coin</label>
						
						<select name='coin_id' class='form-control' required>

							@foreach($coins as $coin)

								<option value='{{ $coin->id }}' > {{ $coin->name }} </option>

							@endforeach

						</select>

					</div>

			

					 <div class='form-group'>
						<label>Minimum GBP price</label>
						<input type='text' name='gbp_min_price' class='form-control' />
					</div>


					 <div class='form-group'>
						<label>Minimum GBP value</label>
						<input type='text' name='gbp_min_value' class='form-control' />
					</div>


					 <div class='form-group'>
						<label>Maximum GBP price</label>
						<input type='text' name='gbp_max_price' class='form-control' />
					</div>


					 <div class='form-group'>
						<label>Maximum GBP value</label>
						<input type='text' name='gbp_max_value' class='form-control' />
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