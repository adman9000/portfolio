@extends('layouts.app')

@section('content') 

<div class="container" >
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">

    

                <div class="panel-heading">Kraken</div>

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

                <p>Buy & sell crypto with Euros on Kraken</p>

                <table class='table table-bordered'>
                <thead><tr><th>Code</th><th>Balance</th><th>Buy / Sell</th></tr></thead>
                <tbody>

                @foreach($balances as $code=>$balance)

                	<tr><td> {{ $code }} </td><td>{{ $balance }}</td>

                	<td>

                		@if ( $code == 'ZEUR' ) 

							<form method='post' action=''>
	                		{{csrf_field()}}
	                		<input type='hidden' name='action' value='buy'>
	                		<input type='hidden' name='coin_2' value='{{ $code }}' />
	                		<select name='coin_1' >
	                		 	@foreach($balances as $mycode=>$mybalance)
	                		 		@if( $mycode != "ZEUR") <option value='{{ $mycode }}'>{{ $mycode }}</option> @endif
	                		 	@endforeach
	                		 </select>

	                		<input type='number' name='volume' value='{{ $balance }}' step='any' />
	                		<input type='submit' class='btn btn-xs btn-warning' value='Buy' >
	                		</form>

					@elseif ( $code == 'BTC' ) 

							<form method='post' action=''>
	                		{{csrf_field()}}
	                		<input type='hidden' name='action' value='buy'>
	                		<input type='hidden' name='coin_2' value='{{ $code }}' />
	                		<select name='coin_1' >
	                		 	@foreach($balances as $mycode=>$mybalance)
	                		 		@if( $mycode != "BTC") <option value='{{ $mycode }}'>{{ $mycode }}</option> @endif
	                		 	@endforeach
	                		 </select>

	                		<input type='number' name='volume' value='{{ $balance }}' step='any' />
	                		<input type='submit' class='btn btn-xs btn-warning' value='Buy' >
	                		</form>


                		@else
                		
	                		<form method='post' action=''>
	                		{{csrf_field()}}
	                		<input type='hidden' name='action' value='sell'>
	                		<input type='hidden' name='coin_1' value='{{ $code }}' />
	                		<input type='hidden' name='coin_2' value='ZEUR' />
	                		<input type='number' name='volume' value='{{ $balance }}' step='any' />
	                		<input type='submit' class='btn btn-xs btn-warning' value='Sell'>
	                		</form>

	                	@endif

                	</td></tr>

               @endforeach


                </div>

            </div>

        </div>

    </div>

</div>

@endsection