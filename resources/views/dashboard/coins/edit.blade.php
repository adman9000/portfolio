@extends('layouts.dashboard')

@section('content') 

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

            	<form method='post' action='/coins/{{ $coin->id }}'>

            	{{csrf_field()}}

            	{{ method_field('PATCH') }}

                <div class="panel-heading">Edit Coin</div>

                <div class="panel-body">

 						<div class='form-group'>
						<label>Exchange (kraken/bittrex)</label>
						<input type='text' name='exchange' class='form-control' value='{{ $coin->exchange }}' />
					</div>

					 <div class='form-group'>
						<label>Coin Code</label>
						<input type='text' name='code' class='form-control' value='{{ $coin->code }}' />
					</div>

					 <div class='form-group'>
						<label>Coin Name</label>
						<input type='text' name='name' class='form-control' value='{{ $coin->name }}' />
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