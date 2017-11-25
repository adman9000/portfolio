@extends('layouts.modal')

@section('title', 'User Management')

@section('content')
      

                <div class="text-center m-t-lg">
             
                   
                   <table class='table table-bordered'>
                    <tr><th>Name</th><td>{{ $record['name'] }}</td></tr>
                    <tr><th>Email</th><td>{{ $record['email'] }}</td></tr>
                    <tr><th>Date Added</th><td>{{ $record['created_at'] }}</td></tr>
                    <tr><th>Last Updated</th><td>{{ $record['updated_at'] }}</td></tr>
                </table>

                

                </div>
@endsection
    
@section('footer')

<div class='modal-footer'>
    <form method="POST" action="" id='form'>
        {{ csrf_field() }}
        <input type='hidden' name='action' value='delete' />
        <input type='submit' value='Delete' class='btn btn-danger' />
    </form>
</div>

@endsection
