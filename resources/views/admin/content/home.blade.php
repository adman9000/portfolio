@extends('layouts.admin')

@section('title', 'Content Management')

@section('content')
  <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="text-center m-t-lg">
                    <h1>
                       Content Management
                    </h1>


    				<div id='record-table'>

                        <table class='table table-bordered datatable'>

                            <thead><tr><th>Page Name</th><th>Elements</th><th>Actions</th></tr></thead>

                            <tbody>

                                @foreach($pages as $page)

                                    <tr><td>{{ $page->title }}</td><td>{{ $page->template->types }}</td><td></td></tr>

                                @endforeach

                            </tbody>

                        </table>

                	</div>

                </div>
            </div>
        </div>
    </div>
@endsection



