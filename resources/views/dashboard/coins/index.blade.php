@extends('layouts.dashboard')

@section('content')


	<div class="container">

		<div class="row">
            <div class="col-md-12">
                <div class="panel panel-default" id='vue-coins'>

                    <div class='panel-heading'><h3 class='panel-title'>My Coins</h3></div>

                    <div class='panel-body'>

                    	<div class='row' style='margin-bottom:15px;'>
                    		<div class='col-xs-6 col-sm-3'>
                    			<select class="form-control filter-button-group">
								  <option data-filter="*">Show all</option>
								  <option data-filter=".wallet">Wallets</option>
								  <option data-filter=":not(.wallet)">Any Exchange</option>
								  <option data-filter=".binance">Binance</option>
								  <option data-filter=".bittrex">Bittrex</option>
								  <option data-filter=".cryptopia">Cryptopia</option>
								  <option data-filter=".kraken">Kraken</option>
								</select>
							</div>
                    		<div class='col-xs-6 col-sm-3'>
								<select name='sort-by' class="form-control sort-by-button-group">
								  <option data-sort-by="original-order">original order</option>
								  <option data-sort-by="name" data-sort-asc='true'>name</option>
								  <option data-sort-by="value" data-sort-asc=0 >GBP value</option>
								</select>

							</div>
						</div>

                    	<div class='row grid'>

	                    	@foreach($coins as $ucoin)

	                    		<div class="col-xs-12 col-sm-6 col-md-4 grid-item {{ $ucoin->exchangeCoin ? $ucoin->exchangeCoin->exchange->slug : 'wallet' }}">

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

				                    			<h4 style='margin:3px;' class='name'>{{ $ucoin->coin->name }} ({{ $ucoin->coin->code }})</h4>

				                    			@if( $ucoin->exchangeCoin) 

				                    				<h6> <b><a href="{{route('exchanges') }}/{{$ucoin->exchangeCoin->exchange->slug}}">{{ $ucoin->exchangeCoin->exchange->title }}</a></b> GBP price &pound;{{ number_format($ucoin->coin->gbp_price, 4)}}</h6>

				                    			@else

				                    				<h6><b><a href="{{route('wallets') }}/{{$ucoin->id}}">Wallet</a></b> GBP price &pound;{{ number_format($ucoin->coin->gbp_price, 4)}}</h6>

				                    			@endif


				                    			<table class='table table-condensed table-bordered'>
				                    				<tr><th>Balance</th><td colspan=2>{{ $ucoin->balance }}</td></tr>

				                    				<tr><th>Current </th><td>&pound;<span class='value'>{{ $ucoin->gbp_value }}</span></td></tr>

				                    				<tr>
				                    					<th>1 Hour</th>
				                    					<td @if ($ucoin['gbp_value_1_hour'] < $ucoin['gbp_value']) 
                                                        class='text-success' 
                                                        @else
                                                            class='text-danger' 
                                                        @endif
	                                                    >£{{ number_format($ucoin['gbp_value'] - $ucoin['gbp_value_1_hour'], 2) }}</td>
	                                                </tr>

	                                                <tr>
				                    					<th>1 Day</th>
	                                                    <td @if ($ucoin['gbp_value_1_day'] < $ucoin['gbp_value']) 
	                                                        class='text-success' 
	                                                        @else
	                                                            class='text-danger' 
	                                                        @endif
	                                                    >£{{ number_format($ucoin['gbp_value'] - $ucoin['gbp_value_1_day'], 2) }}</td>
	                                                </tr>

                                                    <tr>
				                    					<th>1 Week</th>
				                    					<td @if ($ucoin['gbp_value_1_week'] < $ucoin['gbp_value']) 
                                                        class='text-success' 
                                                        @else
                                                            class='text-danger' 
                                                        @endif
                                                    >£{{ number_format($ucoin['gbp_value'] - $ucoin['gbp_value_1_week'], 2) }}</td>
                                                	</tr>

				                    				<? /* TODO this is currently crap
				                    				<tr><th>Original Value</th><td>&pound;{{ $ucoin->original_gbp_value }}</td><td>(&pound;{{ number_format($ucoin->coin->original_gbp_price, 2)}})</td></tr>

				                    				<tr><th>Change</th><td colspan=2  @if($ucoin->value_change > 100) class='text-success' @elseif($ucoin->value_change < 100) class='text-danger' @endif >{{ $ucoin->value_change }}%</td></tr>
				                    				*/ ?>


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

<script>

$(document).ready(function() {

	var $grid = $(".grid").isotope({
	  itemSelector: '.grid-item',
	  layoutMode: 'fitRows',
	   getSortData: {
		    name: '.name', // text from querySelector
		    value: function( itemElem ) { // function
		      var weight = $( itemElem ).find('.value').text();
		      return parseFloat( weight.replace( /[\(\)]/g, '') );
		    }
		}
	});

	$grid.isotope({
	  sortAscending: {
	    name: true,
	    value: false
	  }
	});

	// filter items on button click
	$('.filter-button-group').on( 'click', 'option', function() {
	  var filterValue = $(this).attr('data-filter');
	  $grid.isotope({ filter: filterValue });
	});

	// sort items on button click
	$('.sort-by-button-group').on( 'click', 'option', function() {
	  var sortByValue = $(this).attr('data-sort-by');
	  $grid.isotope({ sortBy: sortByValue});
	});


});

</script>


@endsection