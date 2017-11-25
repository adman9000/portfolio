@extends('layouts.modal')

@section('title', 'User Management')

@section('content')
      
    <form method="POST" action="/admin/users/edit/{{$record['id']}}" id='modal-form' data-async=1 data-target='#modal-msg' >
        {{ csrf_field() }}
         <input type='hidden' name='action' value='update' />
  

         <div class='form-group'>
            <label>Name</label>
            <input type='text' required name='name' class='form-control' value="{{ $record['name']}}" />
         </div>
         <div class='form-group'>
            <label>Email</label>
            <input type='email' required name='email' class='form-control' value="{{ $record['email']}}" />
         </div>

</form>

@endsection
    
@section('footer')

<div class='modal-footer'>
     <button class='btn btn-primary submitform' data-form='#modal-form' >Submit</button>
    
</div>
@endsection
