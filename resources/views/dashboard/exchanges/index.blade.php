@extends('layouts.dashboard')

@section('content') 

<div class="container" >
    <div class="row">
        <div class="col-md-12 ">
            <div class="panel panel-default">

    

                <div class="panel-heading">Your Exchanges</div>

                <div class="panel-body">

                <table class='table'>

                  <thead><tr><th>Exchange</th><th>BTC Value</th><th>GBP Value</th></tr></thead>

                  <tbody>

                  @foreach($stats as $exchange => $details)

                      @if($exchange != "total")

                    <tr>
                      <td><a href="{{ route('dashboard') }}/exchanges/{{ $exchange }}"'">{{ $exchange }}</a></td>
                      <td>{{ $details['btc_value']}} BTC</td>
                      <td>&pound;{{ $details['gbp_value'] }}</td>
                    </tr>

                    @endif

                  @endforeach

                </tbody>

                </table>

                </div>
               </div>
              </div>
              </div>
              </div>

             @endsection