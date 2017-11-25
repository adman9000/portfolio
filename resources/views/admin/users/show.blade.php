@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
  <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="text-center m-t-lg">
                    <h1>
                       User Management
                    </h1>
                    <small>View User</small>


                   <table class='table table-bordered'>
                    <tr><th>Name</th><td>{{ $record['name'] }}</td></tr>
                    <tr><th>Email</th><td>{{ $record['email'] }}</td></tr>
                    <tr><th>Date Added</th><td>{{ $record['created_at'] }}</td></tr>
                    <tr><th>Last Updated</th><td>{{ $record['updated_at'] }}</td></tr>
                </table>

                <form method="POST" action="" id='form'>
                    {{ csrf_field() }}
                    <input type='hidden' name='action' value='delete' />
                    <input type='submit' value='Delete' class='btn btn-danger' />
                </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')


@endsection

