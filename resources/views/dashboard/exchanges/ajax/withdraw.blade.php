@extends('layouts.modal')

@section('title') 


	{{ $exchange->title }} {{ $coin->name }} Withdrawal

@endsection

@section('content') 

     <form method='post' action='/dashboard/exchanges/{{ $exchange->slug }}'>

	     {{csrf_field()}}

	     <input type='hidden' name='action' value='withdraw' />
	     <input type='hidden' name='user_coin_id' value='{{ $user_coin->id }}' />

		<div class='form-group'>
			<label>Amount</label>
			<input type='text' name='amount' class='form-control' value='{{ $user_coin->balance }}' />
		</div>

		<div class='form-group'>
			<label>Wallet Address</label>
			<input type='text' name='address' class='form-control' />
		</div>

		<input type='submit' class='btn btn-primary btn-sm' value='Withdraw' />

	</form>


@endsection