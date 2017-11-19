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
                    <small>List of registered users</small>

<a href='/admin/users/create' class='btn btn-sm btn-primary'>Create User</a>

    <div id='record-table'>

                    <?=$record_table?>

                </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')


@endsection

