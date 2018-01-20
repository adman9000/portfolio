@extends('layouts.dashboard')

@section('content')


	<div class="container">

		<div class="row">
            <div class="col-md-12">
                <div class="panel panel-default" id='vue-coins'>

                    <div class='panel-heading'><h3 class='panel-title'>My Coins</h3></div>

                    <div class='panel-body'>

                    	<div class='row'>

	                    	@foreach($coins as $ucoin)

	                    		<div class=' col-xs-12 col-sm-4'>

		                    		<div class='well' style='padding:10px;'>

		                    			<div class='row'>

				                    			<!--
		                    				<div class='col-xs-3'>

				                    			@if($ucoin->coin->gbp_price)

				                    				<p>Price: &pound;{{ number_format($ucoin->coin->gbp_price, 2) }}</p>

				                    			@endif

				                    			@if($ucoin->coin->original_gbp_price)

				                    				<p>Original Price: &pound;{{ number_format($ucoin->coin->original_gbp_price, 2) }}</p>

				                    			@endif


				                    		</div>
				                    			!-->

				                    		<div class='col-xs-12'>

				                    			<h4 style='margin:3px;'>{{ $ucoin->coin->name }} ({{ $ucoin->coin->code }})</h4>

				                    			@if( $ucoin->exchangeCoin) 

				                    				<h6> {{ $ucoin->exchangeCoin->exchange->title }} </h6>

				                    			@else

				                    				<h6>Wallet</h6>

				                    			@endif


				                    			<table class='table table-condensed table-bordered'>
				                    				<tr><th>Balance</th><td>{{ $ucoin->balance }}</td></tr>
				                    				<tr><th>Current Value</th><td>&pound;{{ $ucoin->gbp_value }}</td></tr>


				                    				<tr><th>Original Value</th><td>&pound;{{ $ucoin->original_gbp_value }}</td></tr>
				                    				<tr><th>Change</th><td @if($ucoin->value_change > 100) class='text-success' @elseif($ucoin->value_change < 100) class='text-danger' @endif >{{ $ucoin->value_change }}%</td></tr>


				                    			</table>

				                    		</div>
		                    			</div>

		                    		</div>

		                    	</div>

	                    	@endforeach

	                    </div>

                    </div>

                </div>

            </div>
        </div>
	 
	</div>


@endsection

@section('footer_scripts')



@endsection