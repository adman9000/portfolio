@extends('layouts.main')

@section('content')
<div class="container">

       <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default" id='vue-coins'>

                    <div class='panel-heading'><h3 class='panel-title'>Coin List</h3></div>

                    <div class='panel-body'>

                        <p>Full list of all coins currently available on site. Prices are updated every 5 minutes (no auto-refresh)</p>


                        <table class='table table-bordered table-striped table-condensed datatable'>
                            <thead><tr>

                            <th>Num</th>

                            <th>Code</th>

                            <th>Name</th>


                            <th>BTC Price</th>
                            <th>USD Price</th>
                            <th>GBP Price</th>
                            <th>Current Supply</th>
                            <th>Market Cap (GBP)</th>


                            <th width=200></th>

                            </tr></thead>
                            <tbody>

                                <tr v-for="coin in coins">
                                    
                                        <td >@{{ coin.i }} </td> 
                                        <td >@{{ coin.code }} </td> 
                                        <td >@{{ coin.name }} </td> 
                                        <td >@{{ coin.btc_price }}</td> 
                                        <td >$@{{ coin.usd_price }}</td> 
                                        <td >&pound;@{{ coin.gbp_price }}</td> 
                                        <td >@{{ formatSupply(coin.current_supply) }}</td> 
                                        <td >&pound;@{{ formatPrice(coin.market_cap) }}</td> 
                                        
                                        <td align=right> 
                                             <a href='' class='btn btn-xs btn-info' v-bind:href="coin.url">View</a>
                                        </td></tr>

                            </tbody>

                            <tfoot><tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr></tfoot>

                        </table>

                        <br />
                        <a href='{{ route('dashboard') }}/coins/create' class='btn btn-info'>Add Coin</a>
                        <br />
                    </div>
                </div>
            </div>
        </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                @if (Auth::check())
                       
                       Hi {{Auth::user()->name}}

                         <p>
                    @hasanyrole('member|administrator')
                        I am either a member or an admin or both!
                    @else
                        I am a nobody
                    @endhasanyrole
                    </p>
                    <p>
                    @role('member')
                        I am a member!
                    @else
                        I am not a member...
                    @endrole
                </p>
                <p>
                    @role('administrator')
                        <a href='{{ route('admin') }}'>Admin</a>
                    @else
                        Not an admin
                    @endrole
                </p>

                    @else

                        <a href="{{ url('/login') }}">Login</a> or <a href="{{ url('/register') }}">Register</a>
                        
                    @endif



                </div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('footer_scripts')
<script>
    var i=0;
    var app = new Vue({
      el: '#vue-coins',
      data: 
      {
        coins: [

          @foreach ($coins as $coin)

            { 
             i: ++i ,
             id: "{{$coin['id']}}" ,
             code: "{{$coin['code']}}" ,
             name: "{{$coin['name']}}" ,
             btc_price: "{{$coin->latestCoinPrice['btc_price']}}" ,
             usd_price: "{{$coin->latestCoinPrice['usd_price']}}" ,
             gbp_price: "{{$coin->latestCoinPrice['gbp_price']}}" ,
             current_supply: "{{$coin->latestCoinPrice['current_supply']}}" ,
             market_cap: "{{$coin->latestCoinPrice['current_supply'] * $coin->latestCoinPrice['gbp_price']}}" ,
             url: "{{ route('dashboard') }}/coins/{{ $coin->id }}"
           },

           @endforeach

          ]
        },
        methods :{
            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },
             formatSupply(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            }
        }
    })

</script>
@endsection