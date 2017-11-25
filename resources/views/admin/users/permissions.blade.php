@extends('layouts.admin')

@section('title', 'User Permissions page')

@section('content')

  <div class="wrapper wrapper-content animated fadeInRight">

<div class='row'>

	<div class='col-xs-6'>


		<form method="POST" action="" id='form'>
		    {{ csrf_field() }}
		    <input type='hidden' name='action' value='set_permissions' />

		    <h4>Roles</h4>
		    <div class='checkbox'>
		    	<label>
		    	<input type='checkbox' name='roles[]'  value="administrator" <?=$record->hasRole("administrator") ? "CHECKED" : ""?> />
		    	Administrator</label>
		     </div>

		      <div class='checkbox'>
		    	<label>
		    	<input type='checkbox' name='roles[]'  value="member" <?=$record->hasRole("member") ? "CHECKED" : ""?> />
		    	Member</label>
		     </div>

		       <h4>Permissions</h4>
			    <div class='checkbox'>
			    	<label><input type='checkbox' name='permissions[]' value="edit content" <?=$record->hasPermissionTo("edit content") ? "CHECKED" : ""?> />
			    	Edit Content</label>
			    </div>

 				<div class='checkbox'>
			    	<label><input type='checkbox' name='permissions[]' value="edit users" <?=$record->hasPermissionTo("edit users") ? "CHECKED" : ""?> />
			    	Edit Users</label>
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

</script>

@endsection