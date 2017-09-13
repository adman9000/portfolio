@extends('layouts.app')

@section('content') 

<div class="container" >
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">

    

                <div class="panel-heading">Kraken</div>

                <div class="panel-body">

                <ul>
                <li><a href="{{route('kraken')}}">Kraken</a></li>
                <li><a href="{{route('bittrex')}}">Bittrex</a></li>
                </ul>

                </div>
               </div>
              </div>
              </div>
              </div>

             @endsection