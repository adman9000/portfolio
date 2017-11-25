
<form method='post' action='/schemes/{{ $scheme->id }}/ajax/sellcoin'   data-async=1 >
	{{csrf_field()}}

			<input type='hidden' name='coin_scheme_id' value='{{$pivot->id}}' />


	<div class='modal-header'> <h3 class='modal-title'>{{ $scheme->title }} - {{ $coin->code }}</h3> </div>

	<div class='modal-body'>

		<div id='msg'></div>

		<p>Click confirm to sell this coin in this scheme</p>

		<div class='form-group'>
			<label>Amount Held</label>
			<p class='form-control-static'> {{ $pivot->amount_held }} </p>
		</div>
		
		<div class='checkbox'>
			<label><input type='checkbox' name='delete' value=1 /> Delete coin from scheme</label>
		</div>
	</div>

	<div class='modal-footer'>

		<input type='submit' class='btn btn-primary btn-sm' value='Confirm' />

	</div>

</form>
