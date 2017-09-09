@extends('layouts.app')

@section('content') 

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

    

                <div class="panel-heading">View Coin</div>

                <div class="panel-body">

					 <div class='form-group'>
						<label>Coin Code</label>
						{{ $coin->code }}
					</div>

					 <div class='form-group'>
						<label>Coin Name</label>
						{{ $coin->name }}
					</div>


				</div>


			</div>
		</div>
	</div>
</div>

@endsection