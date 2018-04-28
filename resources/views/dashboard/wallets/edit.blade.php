@extends('layouts.dashboard')

@section('content') 

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

            	<form method='post' action='{{ route('dashboard') }}/wallets/{{ $wallet->id }}'>

            	{{csrf_field()}}
            	{{ method_field('PATCH') }}

                <div class="panel-heading">Edit a Wallet</div>

                <div class="panel-body">

					 <div class='form-group'>
						<label>Coin</label>
						
						<select name='coin_id' class='form-control' required>

							@foreach($coins as $coin)

								<option value='{{ $coin->id }}' {{ $wallet->coin_id == $coin->id ? "SELECTED" : "" }} > {{ $coin->name }} </option>

							@endforeach

						</select>

					</div>

			

					 <div class='form-group'>
						<label>Balance</label>
						<input type='text' name='balance' class='form-control' value="{{ $wallet->balance }}" />
					</div>

					 <div class='form-group'>
						<label>Address</label>
						<input type='text' name='address' class='form-control' value="{{ $wallet->address }}" />
					</div>

					 <div class='form-group'>
						<label>Notes</label>
						<input type='text' name='notes' class='form-control' placeholder="Ledger, Paper etc" value="{{ $wallet->notes }}" />
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