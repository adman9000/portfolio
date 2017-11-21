@extends('layouts.app')

@section('content') 

<div class="container" >
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">

    

                <div class="panel-heading">{{ $exchange['title'] }}</div>

                <div class="panel-body">


	                @if( isset($order_error) )

	                	{{ $order_error }}

	                @endif

    			@if( isset($order_description) )

	                	{{ $order_description }}

	                @endif

    			@if( isset($order_txid ))

	                	{{ $order_txid }}

	                @endif

                <p>Buy & sell crypto on {{ $exchange['title'] }}</p>

                <table class='table table-bordered'>
                <thead><tr><th>Code</th><th>Balance</th><th>Available</th><th>Locked</th><th>BTC Value</th><th>GBP Value</th><th>Buy / Sell</th></tr></thead>
                <tbody>

                @foreach($stats['assets'] as $asset)

                	<tr><td> {{ $asset['code'] }} </td><td>{{ $asset['balance'] }}</td><td>{{ $asset['available'] }}</td><td>{{ $asset['locked'] }}</td><td>{{ $asset['btc_value'] }}</td><td>&pound;{{ $asset['gbp_value'] }}</td>

					<td>

                		@if ( $asset['code'] == 'ZEUR' ) 

							<form method='post' action=''>
	                		{{csrf_field()}}
	                		<input type='hidden' name='action' value='buy'>
	                		<input type='hidden' name='coin_2' value='{{ $asset['code'] }}' />
	                		<select name='coin_1' >
	                		 	@foreach($balances as $myasset)
	                		 		@if( $myasset['code'] != "ZEUR") <option value='{{ $myasset.code }}'>{{ $myasset['code'] }}</option> @endif
	                		 	@endforeach
	                		 </select>

	                		<input type='number' name='volume' value='{{ $myasset['balance'] }}' step='any' />
	                		<input type='submit' class='btn btn-xs btn-warning' value='Buy' >
	                		</form>

					@elseif ( $asset['code'] == 'BTC' ) 

							<form method='post' action=''>
	                		{{csrf_field()}}
	                		<input type='hidden' name='action' value='buy'>
	                		<input type='hidden' name='coin_2' value='{{ $asset['code'] }}' />
	                		<select name='coin_1' >
	                		 	@foreach($balances as $myasset)
	                		 		@if( $myasset['code'] != "BTC") <option value='{{ $myasset['code'] }}'>{{ $myasset['code'] }}</option> @endif
	                		 	@endforeach
	                		 </select>

	                		<input type='number' name='volume' value='{{ $asset['balance'] }}' step='any' />
	                		<input type='submit' class='btn btn-xs btn-warning' value='Buy' >
	                		</form>


                		@else
                		
	                		<form method='post' action=''>
	                		{{csrf_field()}}
	                		<input type='hidden' name='action' value='sell'>
	                		<input type='hidden' name='coin_1' value='{{ $asset['code'] }}' />
	                		<input type='hidden' name='coin_2' value='ZEUR' />
	                		<input type='number' name='volume' value='{{ $asset['balance'] }}' step='any' />
	                		<input type='submit' class='btn btn-xs btn-warning' value='Sell'>
	                		</form>

	                	@endif

                	</td>

                	</tr>

               @endforeach


                </div>

            </div>

        </div>

    </div>

</div>

@endsection