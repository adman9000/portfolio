
<form method='post' action='/schemes/{{ $scheme->id }}/ajax/updatecoin'  ng-app='myApp' ng-controller='modalCtrl' data-async=1 >
	{{csrf_field()}}

			<input type='hidden' name='coin_scheme_id' value='{{$pivot->id}}' />

			<input type='hidden' id='modal-buy-percent' value='{{$scheme->buy_drop_percent}}' />
			<input type='hidden' id='modal-trigger-1-percent' value='{{$scheme->sell_1_gain_percent}}' />
			<input type='hidden' id='modal-trigger-2-percent' value='{{$scheme->sell_2_gain_percent}}' />

	<div class='modal-header'> <h3 class='modal-title'>{{ $scheme->title }} - {{ $coin->code }}</h3> </div>

	<div class='modal-body'>

		<div id='msg'></div>

		<div class='form-group'>
			<label>Current Price</label>
			<p class='form-control-static'> {{ $coin->latestCoinPrice->current_price }} </p>
		</div>

		<div class='form-group'>
			<label>Baseline Price</label>
			<input type='text' class='form-control' name='set_price' value='{{$pivot->set_price}}' id='modal-set-price' />
		</div>

		<div class='form-group'>
			<label>Buy Price</label>
			<input type='text' class='form-control' name='' disabled=true readonly=true id='modal-buy-price' />
		</div>

		<div class='form-group'>
			<label>Trigger 1 Price</label>
			<input type='text' class='form-control' name='' disabled=true readonly=true id='modal-trigger-1-price' />
		</div>

		<div class='form-group'>
			<label>Trigger 2 Price</label>
			<input type='text' class='form-control' name='' disabled=true readonly=true id='modal-trigger-2-price' />
		</div>

	</div>

	<div class='modal-footer'>

		<input type='submit' class='btn btn-primary btn-sm' value='Save' />

	</div>

</form>

<script>
	$(document).ready(function() {
		setModalPrices();
	})
</script>