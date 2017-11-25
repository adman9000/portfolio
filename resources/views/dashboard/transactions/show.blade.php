@extends('layouts.dashboard')

@section('content') 

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

                <div class="panel-heading">View Transaction</div>

                <div class="panel-body">
	
					<div class='form-group'>
						<label>Scheme</label>
						<p class='form-control-static'><a href='/schemes/{{ $transaction->scheme->id }}'>{{ $transaction->scheme->title }}</a></p>
					</div>
					
					<div class='form-group'>
							<label>Transaction ID</label>
						<p class='form-control-static'>{{ $transaction->uuid }}</p>
					</div>

					 <div class='form-group'>
						<label>Date</label>
						<p class='form-control-static'>{{ $transaction->created_at }} </p>
					</div>

					 <div class='form-group'>
						<label>Bought</label>
						<p class='form-control-static'>{{ $transaction->amount_bought }} {{$transaction->coinBought ? $transaction->coinBought->code : "BTC" }}</p>
					</div>

					<div class='form-group'>
						<label>Sold</label>
						<p class='form-control-static'>{{ $transaction->amount_sold }} {{$transaction->coinSold ? $transaction->coinSold->code : "BTC" }}</p>
					</div>

					<div class='form-group'>
						<label>Rate</label>
						<p class='form-control-static'>{{ $transaction->exchange_rate }} </p>
					</div>

					<div class='form-group'>
						<label>Status</label>
						<p class='form-control-static'>{{ $transaction->status }}</p>
					</div>


				</div>
			</div>
		</div>
	</div>
</div>

@endsection