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

                <table class='table table-bordered datatable'>
                <thead><tr><th>Code</th><th class='hidden-xs hidden-sm'>BTC Price</th><th>GBP Price</th><th>Balance</th><th class='hidden-xs hidden-sm'>Available</th><th class='hidden-xs hidden-sm'>Locked</th><th class='hidden-xs hidden-sm'>BTC Value</th><th>GBP Value</th> @can('trade')<th>Sell</th>@endcan </tr></thead>
                <tbody>

                @foreach($stats['assets'] as $asset)

                	

	                	<tr>
                      <td> {{ $asset['code'] }} </td>
                      <td class='hidden-xs hidden-sm'>{{ $asset['btc_price'] }}</td>
                      <td>{{ $asset['gbp_price'] }}</td>
                      <td>{{ $asset['balance'] }}</td>
                      <td class='hidden-xs hidden-sm'>{{ $asset['available'] }}</td>
                      <td class='hidden-xs hidden-sm'>{{ $asset['locked'] }}</td>
                      <td class='hidden-xs hidden-sm'>{{ $asset['btc_value'] }}</td>
                      <td data-order="{{ $asset['gbp_value']}}">&pound;{{ $asset['gbp_value'] }}</td>


                      @can('trade')
						<td>

	     
	                		
		                		<form method='post' action='' class='form form-inline'>
		                		{{csrf_field()}}
		                		<input type='hidden' name='action' value='sell'>
		                		<input type='hidden' name='user_coin_id' value='{{ $asset['user_coin_id'] }}' />
                        <div class='form-group'>
                          <div  class='input-group'>
		                		    <input type='number' name='volume' value='{{ $asset['balance'] }}' placeholder='0.00' step='any' class='form-control' style='min-width:120px;' />
		                		    <span class='input-group-btn'>
                              <input type='submit' class='btn btn-warning' value='Sell'>
                            </span>
                          </div >
                        </div>
		                		</form>

                  @if($asset['btc_price']>0)

                    <form method='post' action='' class='form form-inline'>
                        {{csrf_field()}}
                        <input type='hidden' name='action' value='buy'>
                        <input type='hidden' name='user_coin_id' value='{{ $asset['user_coin_id'] }}' />
                        <input type='hidden' name='coin_id' value='{{ $asset['coin_id'] }}' />
                        <input type='hidden' name='exchange_coin_id' value='{{ $asset['id'] }}' />
                        <input type='hidden' name='user_exchange_id' value='{{ $user_exchange['id'] }}' />
                        <div class='form-group'>
                          <div  class='input-group'>
                            <input type='number' name='volume' value="{{ $stats['btc']['balance'] / $asset['btc_price'] }}" step='any' class='form-control'  placeholder='0.00' style='min-width:120px;'  />
                            <span class='input-group-btn'>
                              <input type='submit' class='btn btn-warning' value='Buy'>
                            </span>
                          </div >
                        </div>
                        </form>

                  @endif

                      <!-- View Address !-->
                      <a href="{{route('exchanges')}}/{{$exchange->id}}/address/{{$asset['coin_id']}}" data-toggle='modal-ajax' class='btn btn-info btn-sm'>View Address</a>

                      <!-- Withdrawal !-->
                      <a href="{{route('exchanges')}}/{{$exchange->id}}/withdraw/{{$asset['coin_id']}}/{{$asset['user_coin_id']}}" data-toggle='modal-ajax' class='btn btn-info btn-sm'>Withdrawal</a>

                      <!-- History !-->
                      <a href="{{route('exchanges')}}/{{$exchange->id}}/history/{{$asset['coin_id']}}/{{$asset['user_coin_id']}}" data-toggle='modal-ajax' class='btn btn-info btn-sm'>History</a>

	                	</td>

                    @endcan

	                	</tr>


               @endforeach

           </tbody>
       </table>

       <form method='post' action="">
              {{ csrf_field() }}
            <input type='hidden' name='action' value='rescan' />
            <input type='submit' value='Scan for Coins' class='btn btn-sm btn-primary' />
        </form>

      <form method='post' action="">
              {{ csrf_field() }}
            <input type='hidden' name='action' value='resync' />
            <input type='submit' value='Resync Balances' class='btn btn-sm btn-primary' />
        </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection