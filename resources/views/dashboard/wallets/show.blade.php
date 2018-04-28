@extends('layouts.dashboard')

@section('content') 

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">


                <div class="panel-heading">View Wallet</div>

                <div class="panel-body">

					 <div class='form-group'>
						<label>Coin</label>
						
						<p class='form-control-static'>{{ $wallet->coin->name }} ({{$wallet->coin->code}})</p>

					</div>

			

					 <div class='form-group'>
						<label>Balance</label>
						<p class='form-control-static'>{{ $wallet->balance }}</p>
					</div>

					 <div class='form-group'>
						<label>Address</label>
						<p class='form-control-static'>{{ $wallet->address }}<br />
							<a href="https://ethplorer.io/address/{{ $wallet->address }}" target="_blank">Ethplorer (ERC20 tokens only)</a>
						</p>

					</div>

					 <div class='form-group'>
						<label>Notes</label>
						<p class='form-control-static'>{{ $wallet->notes }}</p>
					</div>


				</div>

			</div>
		</div>
	</div>
</div>

@endsection