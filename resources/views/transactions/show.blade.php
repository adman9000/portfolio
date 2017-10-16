@extends('layouts.app')

@section('content') 

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
			
                <div class="panel-heading">View Transaction</div>

                <div class="panel-body">
	
					<div class='form-group' >
						<label>Scheme</label>
						<p class='form-control-static'>{{ $transaction->scheme->title }} </p>
					</div>
					
					 <div class='form-group'>
						<label>Transaction ID</label>
						<p class='form-control-static'>{{ $transaction->uuid }}</p>
					</div>

					 <div class='form-group'>
						<label>Created At</label>
						<p class='form-control-static'>{{ $transaction->created_at }}</p>
					</div>

					<div class='form-group'>
						<label>Coin Sold</label>
						<p class='form-control-static'>
						{{ $transaction->amount_sold }}
						@if($transaction->coinSold)
							{{ $transaction->coinSold->code }}
						@else
							BTC
						@endif
						</p>
					</div>

					<div class='form-group'>
						<label>Coin Bought</label>
						<p class='form-control-static'>
						{{ $transaction->amount_bought }}
						@if($transaction->coinBought)
							{{ $transaction->coinBought->code }}
						@else
							BTC
						@endif
						</p>
					</div>
					
					<div class='form-group'>
						<label>Rate</label>
						<p class='form-control-static'>{{ $transaction->exchange_rate }}</p>
					</div>
					
				</div>

			</div>
		</div>
	</div>
</div>

@endsection