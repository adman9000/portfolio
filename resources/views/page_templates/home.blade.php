@extends('layouts.main')

@section('content')

<div class="container" id='dashboardApp'>
        <div class="row">

            <div class='col-xs-12'>

                <cointable :coins="coins"></cointable>

            </div>

    <? /*
            <table class='table table-bordered table-condensed'>
                <thead><tr><th>Coin</th><th>Price</th><th>24 hr change</th><th>24 hr volume</th></tr></thead>

                <tbody>

                     @foreach ($coins as $coin)

                        <tr style='background-color:black;'><td><h2 class="price-display">{{ $coin['FullName'] }}</h2></td>
                            <td><span class="price" id="PRICE_{{ $coin['Symbol'] }}"></span></td>
                            <td><span id="CHANGE24HOUR_{{ $coin['Symbol'] }}"></span><span id="CHANGE24HOURPCT_{{ $coin['Symbol'] }}"></span></td>
                            <td><span id="VOLUME24HOURTO_{{ $coin['Symbol'] }}"></span></td>
                        </tr>

                    @endforeach

                </tbody>

            </table>
*/ ?>

<? /*

     @foreach ($coins as $coin)

            <div class="col-md-4 price-boxes">
                <div class="panel-group">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <h6><a href="https://www.cryptocompare.com">Source: CryptoCompare.com</a></h6>
                            <h2 class="price-display">{{ $coin['code'] }} - USD <span class="price" id="PRICE_{{ $coin['code'] }}"></span></h2>
                            <h5>24h Change: <span id="CHANGE24HOUR_{{ $coin['code'] }}"></span><span id="CHANGE24HOURPCT_{{ $coin['code'] }}"></span><br></h5>
                            <h5>Last Market: <span class="exchange" id="LASTMARKET_{{ $coin['code'] }}"></span> <br></h5>
                            <h5>Trade ID: <span id="LASTTRADEID_{{ $coin['code'] }}"></span><br></h5>
                            <h5>Open Hour: <span id="OPENHOUR_{{ $coin['code'] }}"></span><br></h5>
                            <h5>High Hour: <span id="HIGHHOUR_{{ $coin['code'] }}"></span><br></h5>
                            <h5>Low Hour: <span id="LOWHOUR_{{ $coin['code'] }}"></span><br></h5>
                            <h5>Open Day: <span id="OPEN24HOUR_{{ $coin['code'] }}"></span><br></h5>
                            <h5>High Day: <span id="HIGH24HOUR_{{ $coin['code'] }}"></span><br></h5>
                            <h5>Low Day: <span id="LOW24HOUR_{{ $coin['code'] }}"></span><br></h5>
                            <h5>Last Trade Volume: <span id="LASTVOLUME_{{ $coin['code'] }}"></span><br></h5>
                            <h5>Last Trade Volume To: <span id="LASTVOLUMETO_{{ $coin['code'] }}"></span><br></h5>
                            <h5>24h Volume: <span id="VOLUME24HOUR_{{ $coin['code'] }}"></span><br></h5>
                            <h5>24h VolumeTo: <span id="VOLUME24HOURTO_{{ $coin['code'] }}"></span><br></h5>
                        </div>
                    </div>
                </div>
            </div>

        @endforeach
*/ ?>

     
    </div>
</div>

<? /*
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

*/ ?>
@endsection



@section('footer_scripts')


    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.7.2/socket.io.js"></script>
    <script src="/cryptocompare/ccc-streamer-utilities.js"></script>

    <script>


    var subscription = [];

     @foreach ($coins as $coin)

     subscription.push('5~CCCAGG~{{ $coin['Symbol'] }}~USD');

     @endforeach


//TODO: Replace this with generic datum class?
class Coin {

    constructor(data) {
        this.original_data = data;

        for(let field in data)
            this[field] = data[field];
    }

    update(data) {

        for(let field in data)
            this[field] = data[field];   
    }
    
}

//TODO: Replace this with generic data class?
class Coins {

    constructor() {
        this.coins = [];
    }

    addCoin(data) {
        this.coins.push(new Coin(data));
    }

    //Add domain to data array or update if already exists
    importCoin(data) {

        //data = $.parseJSON(data);
        var datum = {
            "code" : htmlEncode(data['FROMSYMBOL']),
            "name" : htmlEncode(data['FROMSYMBOL']),
            "price" : data['PRICE'],
            "change" : data['CHANGE24HOUR'],
            "volume" : CCC.convertValueToDisplay("$", data['VOLUME24HOURTO']),
        };
        var updated=false;


        if(parseFloat(datum['price'])>0) {
            for(var i=0;i<this.coins.length;i++) {
                if(datum['code'] == this.coins[i].code) {
                    this.coins[i].update(datum);
                    updated = true;
                }
            }

            if(!updated) this.addCoin(datum);
        }

    }

    importCoins(data) {

        for(var i=0;i<data.length;i++) {

            this.importCoin(data[i]);

        }
    }

    doFilter(type, val) {
        //Default to all domains
        var arr = this.coins;
        console.log(val);

        //Do keyword filter afterwards
        if(val)
            arr = this.filter(val, arr);

        return arr;
    }

    //TODO: Needs to filter on all data
    filter(val, arr) {
        var myself = this;
        if(!arr) var arr = this.coins;
        arr = arr.filter(function(coin) {
            return coin.code.includes(val);
        });
        return arr;
    }

}

//Domain Table component
    Vue.component('cointable', {

        delimiters: ['[[', ']]'], //required to avoid blade clash

        props : ["type", "coins"], //list the properties we can pass through

        data: function () {
            return {
                sortKey: ['coin'],
                sortOrder: ['asc'],
                filterval : ''
            }
          },

        computed: {
            coinsSorted: function() {

                var arr = this.coins.doFilter(this.type, this.filterval);

                return _.orderBy(arr, this.sortKey, this.sortOrder);
            },
        },

        methods : {
            sortBy: function(key) {
                if (key == this.sortKey) {
                    this.sortOrder = (this.sortOrder == 'asc') ? 'desc' : 'asc';
                } else {
                    this.sortKey = key;
                    this.sortOrder = 'asc';
                }
           },
        },

        template : `
            
            <div>
                <div class='form-group'>
                    <label>Filter</label>
                    <input class='form-control' type='text' name='filter' v-model='filterval'  />
                 </div>

                <table class='table table-bordered'>

                <thead><tr>
                    <th><span class="fa fa-sort" @click="sortBy('name')"> Coin</span> </th>
                    <th><span class="fa fa-sort" @click="sortBy('price')"> Price</span></th>
                    <th><span class="fa fa-sort" @click="sortBy('change')"> 24 Hr Change</span></th>
                    <th><span class="fa fa-sort" @click="sortBy('volume')"> 24 Hr Volume</span></th>
                    <th></th></tr></thead>

                <tbody>

                    <coinrow v-for="coin in coinsSorted" :coin="coin">
   
                    </coinrow>

                </tbody>
                </table>
            </div>
        `
    });


    Vue.component('coinrow', {

        delimiters: ['[[', ']]'], //required to avoid blade clash

        props : ['coin'],

         data: function () {
            return {
            }
          },

        template :`

            <tr>
            <td>[[coin.name]]</td>
            <td>[[coin.price]]</td>
            <td>[[coin.change]]</td>
            <td>[[coin.volume]]</td>
            <td>
            <a :href="coin.view" class='btn btn-sm btn-info'>View</a>
            </td>
            </tr>

        `

    });

    window.myapp = new Vue({
        el: '#dashboardApp',
        delimiters: ["[[","]]"],

        data: {
            coins : new Coins()

        },

         mounted: function () {
            var myself = this;
            /*preload?
            $.ajax({
                url: '/dashboard/coins/listall',
                method: 'GET',
                success: function (data) {
                    myself.coins.push("1");
                },
                error: function (error) {
                    console.log(error);
                }
            });
            */
        }
    });

</script>
    <script src="/cryptocompare/current/stream.js"></script>

<? /*
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
*/ ?>
@endsection