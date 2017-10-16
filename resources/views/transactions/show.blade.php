@extends('layouts.app')

@section('content') 

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

    

                <div class="panel-heading">View Transaction</div>

                <div class="panel-body">

					 <div class='form-group'>
						<label>Date</label>
						{{ $transaction->created_at }} 
					</div>

					 <div class='form-group'>
						<label>Bought</label>
						{{ $transaction->amount_bought }} {{$transaction->coinBought ? $transaction->coinBought->code : "BTC" }}
					</div>

					<div class='form-group'>
						<label>Sold</label>
						{{ $transaction->amount_sold }} {{$transaction->coinSold ? $transaction->coinSold->code : "BTC" }}
					</div>

					<div class='form-group'>
						<label>Rate</label>
						{ $transaction->exchange_rate }} 
					</div>

					<div class='form-group'>
						<label>Status</label>
						{{ $transaction->status }}
					</div>

					<div class='form-group'>
						<label>Scheme</label>
						<a href='/schemes/{{ $transaction->scheme->id }}'>{{ $transaction->scheme->title }}</a>
					</div>

				</div>


			</div>
		</div>
	</div>
</div>

@endsection