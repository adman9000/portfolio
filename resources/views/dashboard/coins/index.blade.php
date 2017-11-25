@extends('layouts.dashboard')

@section('content')


	<div class="container">


	    <div class="row">
	        <div class="col-md-12">
	            <div class="panel panel-default" id='vue-coins'>

	            	<div class='panel-heading'><h3 class='panel-title'>Coin List</h3></div>

	            	<div class='panel-body'>

		            	<p>Full list of all coins currently available on site. Based on the top 100 coins on coinmarketcap plus a few extras. Prices are updated every 5 minutes (no auto-refresh)</p>

						<div class='form-group'>
							<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-search"></i>
						<input type="text" class="form-control" placeholder="Search Coins" ng-model="searchCoins">
						</div></div></div>

						<table class='table table-bordered table-striped table-condensed'>
							<thead><tr>

							<th>Num</th>

							<th>Code</th>

							<th>Name</th>


							<th>BTC Price</th>
							<th>USD Price</th>
							<th>GBP Price</th>
							<th>Current Supply</th>
							<th>Market Cap (GBP)</th>


							<th width=200></th>

							</tr></thead>
							<tbody>

								<tr v-for="coin in coins">
									
										<td >@{{ coin.i }} </td> 
										<td >@{{ coin.code }} </td> 
										<td >@{{ coin.name }} </td> 
										<td >@{{ coin.btc_price }}</td> 
										<td >$@{{ coin.usd_price }}</td> 
										<td >&pound;@{{ coin.gbp_price }}</td> 
										<td >@{{ formatSupply(coin.current_supply) }}</td> 
										<td >&pound;@{{ formatPrice(coin.market_cap) }}</td> 
										
										<td align=right> 
											<? /* <a href='/coins/@{{ coin.id }}' class='btn btn-xs btn-info'>View</a>*/ ?>
										</td></tr>

							</tbody>

							<tfoot><tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr></tfoot>

						</table>

						<br />
						<a href='/coins/create' class='btn btn-info'>Add Coin</a>
						<br />
					</div>
				</div>
			</div>
		</div>
	</div>


@endsection

@section('footer_scripts')


<script>
	var i=0;
	var app = new Vue({
	  el: '#vue-coins',
	  data: 
	  {
	  	coins: [

		  @foreach ($coins as $coin)

		    { 
		     i: ++i ,
		     id: "{{$coin['id']}}" ,
		     code: "{{$coin['code']}}" ,
		     name: "{{$coin['name']}}" ,
		     btc_price: "{{$coin->latestCoinPrice['btc_price']}}" ,
		     usd_price: "{{$coin->latestCoinPrice['usd_price']}}" ,
		     gbp_price: "{{$coin->latestCoinPrice['gbp_price']}}" ,
		     current_supply: "{{$coin->latestCoinPrice['current_supply']}}" ,
		     market_cap: "{{$coin->latestCoinPrice['current_supply'] * $coin->latestCoinPrice['gbp_price']}}" 
		   },

		   @endforeach

		  ]
		},
		methods :{
		    formatPrice(value) {
		        let val = (value/1).toFixed(2).replace(',', '.')
		        return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
		    },
		     formatSupply(value) {
		        let val = (value/1).toFixed(2).replace(',', '.')
		        return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
		    }
		}
	})

</script>

@endsection