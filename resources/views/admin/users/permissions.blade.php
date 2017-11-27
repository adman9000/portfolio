@extends('layouts.admin')

@section('title', 'User Permissions page')

@section('content')

  <div class="wrapper wrapper-content animated fadeInRight">


		<form method="POST" action="" id='form'>
		    {{ csrf_field() }}
		    <input type='hidden' name='action' value='set_permissions' />

<div class='row'>

	<div class='col-sm-4'>

		<div class='panel panel-info'>
			<div class='panel-heading'>
				<h4 class='panel-title'>
		    		<input type='checkbox' name='roles[]'  value="member" <?=$record->hasRole("member") ? "CHECKED" : ""?> />
		    	Member</h4>
		     </div>

		     <div class='panel-body'>

			    <div class='checkbox'>
			    	<label><input type='checkbox' name='permissions[]' value="trade" <?=$record->hasPermissionTo("trade") ? "CHECKED" : ""?> />
			    	Trade</label>
			    </div>

 				<div class='checkbox'>
			    	<label><input type='checkbox' name='permissions[]' value="autotrade" <?=$record->hasPermissionTo("autotrade") ? "CHECKED" : ""?> />
			    	Autotrade</label>
			    </div>

			   </div>
			</div>

		</div>

	<div class='col-sm-8'>

		<div class='panel panel-warning'>
			<div class='panel-heading'>
				<h4 class='panel-title'>
		    		<input type='checkbox' name='roles[]'  value="administrator" <?=$record->hasRole("administrator") ? "CHECKED" : ""?> />
		    	Administrator</h4>
		     </div>

		     <div class='panel-body'>

		     	<p class='text-danger'>Be very careful with the administrator role. This gives users access to the content management system.</p>

		     	<table class='table table-bordered'>

			     	<tr>
			     		<td>
						    <div class='checkbox'>
						    	<label><input type='checkbox' name='permissions[]' value="edit content" <?=$record->hasPermissionTo("edit content") ? "CHECKED" : ""?> />
						    	Edit Content</label>
						    </div>
						</td>
						<td>
							<div class='checkbox'>
						    	<label><input type='checkbox' name='permissions[]' value="publish content" <?=$record->hasPermissionTo("publish content") ? "CHECKED" : ""?> />
						    	Publish Content</label>
						    </div>
						</td>
					</tr>
					<tr>
						<td>

			 				<div class='checkbox'>
						    	<label><input type='checkbox' name='permissions[]' value="view users" <?=$record->hasPermissionTo("view users") ? "CHECKED" : ""?> />
						    	View Users</label>
						    </div>

						</td>
						<td>

							<div class='checkbox'>
						    	<label><input type='checkbox' name='permissions[]' value="edit users" <?=$record->hasPermissionTo("edit users") ? "CHECKED" : ""?> />
						    	Edit Users</label>
						    </div>

						</td>
					</tr>
				</table>

			   </div>
			</div>

		</div>

		
	</div>

<div class='row'>
	<div class='col-xs-4 col-xs-offset-4'>

		    <input type='submit' value='Submit' class='btn btn-primary' />

	</div>

	</form>


</div>
</div>
</div>

@endsection


@section('scripts')

<script>

</script>

@endsection