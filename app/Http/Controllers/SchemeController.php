<?php

namespace App\Http\Controllers;

use App\Scheme;
use App\Coin;
use Illuminate\Http\Request;
use adman9000\Bittrex\Bittrex;

class SchemeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Show an overview of all schemes
        //Which are enabled?
        //Show profit/loss, start date, amount invested

        $data = array();
        $data['schemes'] = Scheme::all();

        return view("schemes.index", $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view("schemes.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
           //Validate input
        $this->validate(request(), [
            'title' => 'required']
            );

        //only safe if fillable/guarded set
        $data = request()->all();
        $data['enabled'] = false;
        Scheme::create($data);

        return $this->index();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\scheme  $scheme
     * @return \Illuminate\Http\Response
     */
    public function show(scheme $scheme)
    {
        //

        $data['scheme'] = $scheme;

         $data['usd_gbp_rate']  = env("USD_GBP_RATE");

        return view("schemes.show", $data);
    }

      /**
     * Display the transaction records & bittrex orders for this scheme
     *
     * @param  \App\scheme  $scheme
     * @return \Illuminate\Http\Response
     */
    public function orders(scheme $scheme)
    {
        //

        $data['scheme'] = $scheme;

       //$orders = Bittrex::getOrderHistory();
       /*foreach($data['scheme']->transactions as $t=>$transaction) {
            foreach($orders['result'] as $order) {
                if($transaction->uuid == $order['OrderUuid']) $data['scheme']->transactions[$t]->_order = $order;
            }
       }*/

        return view("schemes.orders", $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\scheme  $scheme
     * @return \Illuminate\Http\Response
     */
    public function edit(scheme $scheme)
    {
        //

        $data['scheme'] = $scheme;

        //Get latest markets for everythign on bittrex
        $markets = Bittrex::getMarketSummaries();

        $coins = Coin::all();
        //Add current prices to coin data
         foreach($markets['result'] as $market) {
            $arr = explode("-", $market['MarketName']);
            $base = $arr[0];
            if($base == "BTC") {
                foreach($coins as $c=>$coin) {
                    if($coin->code == $arr[1]) {
                        $coins[$c]->baseline_price = number_format($market['Last'], 10, '.', '');
                        break;
                    }
                }
            }
        }

         //Set whether or not each coin is included in scheme already
        foreach($coins as $c=>$coin) {

            foreach($data['scheme']->coins as $mycoin) {
                if($coin->id == $mycoin->id) $coins[$c]->is_included = true;
            }

        }
        $data['coins'] = $coins;

        return view("schemes.edit", $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\scheme  $scheme
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, scheme $scheme)
    {
        //Used for setting enabled coins & baseline prices as well as enabling/disabling scheme
       
        $coins = array();

        //get posted baseline prices
        $baseline_prices = $request->baseline_price;
        $coins_included = $request->coins_included;
        //create array of coin_schemes for the intermediate table

        //loop through enabled ticks posted
        foreach($coins_included as $coin_id) {
            //add baseline price to the array
            $coins[$coin_id] = array('set_price' => $baseline_prices[$coin_id]);
        }

        //Sync this array to the db
        $scheme->coins()->sync($coins);

        return $this->index();
    }


    /**
     * Enable or disable the selectd scheme
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\scheme  $scheme
     * @return \Illuminate\Http\Response
    **/
    public function enable(Request $request, scheme $scheme) {

        if($request->enabled) {
            $scheme->enabled = true;
            $scheme->date_start = date("Y-m-d G:i:s");
        }
        else {
            $scheme->enabled = false;
        }
        $scheme->save();

        return $this->show($scheme);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\scheme  $scheme
     * @return \Illuminate\Http\Response
     */
    public function destroy(scheme $scheme)
    {
        //
    }
}
