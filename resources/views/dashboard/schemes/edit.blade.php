@extends('layouts.dashboard')

@section('content') 

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

            	<form method='post' action='/schemes/{{ $scheme->id }}'>

	            	{{csrf_field()}}
	            	 {{ method_field('PATCH') }}

	                <div class="panel-heading">Amend a Scheme</div>

	                <div class="panel-body">

						 <div class='form-group'>
							<label>Title</label>
							<input type='text' name='title' class='form-control' value="{{ $scheme->title }}" />
						</div>

						 <div class='form-group'>
							<label>Buy drop % </label>
							<input type='text' name='buy_drop_percent' class='form-control' value="{{ $scheme->buy_drop_percent }}"  />
						</div>


						 <div class='form-group'>
							<label>Buy amount</label>
							<input type='text' name='buy_amount' class='form-control' value="{{ $scheme->buy_amount }}"  />
						</div>


						 <div class='form-group'>
							<label>Sell point 1 gain %</label>
							<input type='text' name='sell_1_gain_percent' class='form-control' value="{{ $scheme->sell_1_gain_percent }}"  />
						</div>

						 <div class='form-group'>
							<label>Sell point 1 drop %</label>
							<input type='text' name='sell_1_drop_percent' class='form-control' value="{{ $scheme->sell_1_drop_percent }}"  />
						</div>

						 <div class='form-group'>
							<label>Sell point 1 sale %</label>
							<input type='text' name='sell_1_sell_percent' class='form-control' value="{{ $scheme->sell_1_sell_percent }}"  />
						</div>


						 <div class='form-group'>
							<label>Sell point 2 gain %</label>
							<input type='text' name='sell_2_gain_percent' class='form-control' value="{{ $scheme->sell_2_gain_percent }}"  />
						</div>

						 <div class='form-group'>
							<label>Sell point 2 drop %</label>
							<input type='text' name='sell_2_drop_percent' class='form-control' value="{{ $scheme->sell_2_drop_percent }}"  />
						</div>

						 <div class='form-group'>
							<label>Sell point 2 sale %</label>
							<input type='text' name='sell_2_sell_percent' class='form-control' value="{{ $scheme->sell_2_sell_percent }}"  />
						</div>

						 <div class='form-group'>
							<label>Set price increase on 100% sale</label>
							<input type='text' name='price_increase_percent' class='form-control' value="{{ $scheme->price_increase_percent }}"  />
						</div>

						<div class='form-group'>
							<input type='submit' value='Submit' class='btn btn-primary' />
						</div>

					</div>

				</form>

			</div>
		</div>
	</div>
</div>

@endsection