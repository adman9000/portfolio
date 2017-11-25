@extends('layouts.dashboard')

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
                <thead><tr><th>Code</th><th>Balance</th><th>Available</th><th>Locked</th><th>BTC Value</th><th>GBP Value</th><th>Sell</th></tr></thead>
                <tbody>

                @foreach($stats['assets'] as $asset)

                	@if($asset['btc_value'] > 0.0001)

	                	<tr><td> {{ $asset['code'] }} </td><td>{{ $asset['balance'] }}</td><td>{{ $asset['available'] }}</td><td>{{ $asset['locked'] }}</td><td>{{ $asset['btc_value'] }}</td><td>&pound;{{ $asset['gbp_value'] }}</td>

						<td>

	     
	                		
		                		<form method='post' action='' class='form form-inline'>
		                		{{csrf_field()}}
		                		<input type='hidden' name='action' value='sell'>
		                		<input type='hidden' name='coin_1' value='{{ $asset['code'] }}' />
		                		<input type='hidden' name='coin_2' value='BTC' />
		                		<input type='number' name='volume' value='{{ $asset['balance'] }}' step='any' class='form-control' />
		                		<input type='submit' class='btn btn-sm btn-warning' value='Sell'>
		                		</form>


	                	</td>

	                	</tr>

                	@endif

               @endforeach

           </tbody>
       </table>

       <h3>BTC</h3>

       <table class='table table-bordered'><tr><th>Balance</th><th>Available</th><th>Locked</th><th>GBP Value</th><th>Exchange</th></tr>
       	<tr><td>{{ $stats['btc']['balance'] }}</td><td>{{ $stats['btc']['available'] }}</td><td>{{ $stats['btc']['locked'] }}</td><td>{{ $stats['btc']['gbp_value'] }}</td>
       		<td>
       		<form method='post' action='' class='form form-inline'>
        		{{csrf_field()}}
        		<input type='hidden' name='action' value='buy'>
        		<input type='hidden' name='coin_2' value='BTC' />

        		<div class='form-group'>
	        		<select name='coin_1' class='form-control' >
	        		 	@foreach($stats['assets'] as $myasset)
	        		 		 <option value='{{ $myasset['code'] }}'>{{ $myasset['code'] }}</option>
	        		 	@endforeach
	        		 </select>
        		<input type='number' name='volume' value='{{ $stats['btc']['available'] }}' step='any' class='form-control' />
        		<input type='submit' class='btn btn-sm btn-warning' value='Buy' >
        		</form>
        	</td></tr>
       </table>


                </div>

            </div>

        </div>

    </div>

</div>

@endsection