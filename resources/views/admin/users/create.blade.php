@extends('layouts.admin')

@section('title', 'User Registration page')

@section('content')

  <div class="wrapper wrapper-content animated fadeInRight">

<div class='row'>

	<div class='col-xs-6'>


		<form method="POST" action="" id='form'>
		    {{ csrf_field() }}
		    <input type='hidden' name='action' value='store' />
		    <div class='form-group'>
		    	<label>Name</label>
		    	<input type='text' required name='name' class='form-control' value='' />
		     </div>
		     <div class='form-group'>
		    	<label>Email</label>
		    	<input type='email' required name='email' class='form-control' value='' />
		     </div>
		    <input type='submit' value='Submit' class='btn btn-primary' />
		</form>

	</div>

	<div class='col-xs-6'>

		<div id='ajax-response' class='well'></div>

	</div>

</div>
</div>

@endsection


@section('scripts')

<script>
	$("document").ready(function() {

		//Do something to check for duplicate emails?
	})
</script>

@endsection