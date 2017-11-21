<?php

namespace App\Http\Controllers;

use App\Modules\Portfolio\Scheme;
use App\Modules\Portfolio\Coin;
use App\Modules\Portfolio\CoinScheme;
use Illuminate\Http\Request;
use adman9000\Bittrex\Bittrex;
use Illuminate\Support\Facades\DB;
use App\Repositories\Exchanges;

class SchemeController extends Controller
{

      public function __construct() {
         $this->middleware('auth');
    }
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

        return redirect('schemes')->with('status-success', 'Scheme Created');
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

        //Get BTC invested
        $subtotal = 0;
        foreach($data['scheme']->transactions as $transaction) {

            if($transaction->coin_sold_id == 0) $subtotal += $transaction->amount_sold;
            else if($transaction->coin_bought_id == 0) $subtotal -= $transaction->amount_bought;

        }
        $data['btc_invested'] = $subtotal;
        
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


       //$orders = Bittrex::getOrderHistory();
       /*foreach($data['scheme']->transactions as $t=>$transaction) {
            foreach($orders['result'] as $order) {
                if($transaction->uuid == $order['OrderUuid']) $data['scheme']->transactions[$t]->_order = $order;
            }
       }*/

        return view("schemes.edit", $data);
    }

    /**
     * Select coins and set prices
     *
     * @param  \App\scheme  $scheme
     * @return \Illuminate\Http\Response
     */
    public function coins(scheme $scheme)
    {
        //
        //Load scheme coins
        $scheme->load('coins');

        $data['scheme'] = $scheme;

        //Get latest markets for everythign on bittrex
        $markets = Bittrex::getMarketSummaries();

        $coins = Coin::all();


        //set baseline prices for coins already in the scheme
        foreach($data['scheme']->coins as $scheme_coin) {


            foreach($coins as $c=>$coin) {
                if($coin->id == $scheme_coin->id) {
                    $coins[$c]->baseline_price = $scheme_coin->pivot->set_price;
                }
            }
        }
        //Add current prices to coin data for coins not already in this scheme
         foreach($markets['result'] as $market) {
            $arr = explode("-", $market['MarketName']);
            $base = $arr[0];
            if($base == "BTC") {
                foreach($coins as $c=>$coin) {
                    if($coin->code == $arr[1]) {
                        if(!$coins[$c]->baseline_price) $coins[$c]->baseline_price = number_format($market['Last'], 10, '.', '');
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

        return view("schemes.coins", $data);
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
       
        //Validate input
        $this->validate(request(), [
            'title' => 'required']
            );

        //only safe if fillable/guarded set
        $data = request()->all();
        $scheme->fill($data);
        $scheme->update();

        return redirect('schemes')->with('status-success', 'Scheme Updated');
    }

    /**
     * Set the coins to use in this scheme
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\scheme  $scheme
     * @return \Illuminate\Http\Response
     */
    public function setCoins(Request $request, scheme $scheme)
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

        return redirect('schemes')->with('status-success', 'Coins Updated');
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
     * ajaxView()
     * Handle all ajax page views & form submissions?
     **/
    public function ajaxView(Scheme $scheme, $view, $id) {

        $data = array();
        $data['scheme'] = $scheme;
        $data['coin'] = Coin::find($id);

        //pivot data
        $data['pivot'] = $data['coin']->schemes->find($scheme->id)->pivot;

        return view("schemes.ajax.".$view, $data);
    }

    /**
     * ajaxAction()
     * Handle all ajax page views & form submissions?
     **/
    public function ajaxAction(Request $request, Scheme $scheme, $action) {

        $json = array();

        switch($action) {

            case "updatecoin" :

                DB::table('coin_scheme') -> where('id', $request->get('coin_scheme_id')) -> update(array('set_price' => $request->get('set_price')));

                $json['success'] = "Coin Price updated";

                break;

            case "sellcoin" :

                $exchanges = new Exchanges();

                //Selling up a specified coin for this scheme
                $coinscheme = CoinScheme::find($request->get('coin_scheme_id'));

                $market = Bittrex::getMarketSummary("BTC-".$coinscheme->coin->code);
                $last = $market['result'][0]['Last'];
                $volume = $exchanges->bittrexSell($coinscheme->coin, $coinscheme->amount_held, $last, $coinscheme->scheme);

                //If sale worked, mark as sold on scheme
                if($volume) {
                    if($request->get('delete'))
                        $coinscheme->delete();
                    else {

                        $attributes = [
                            'amount_held' => 0,
                            'been_bought' => 0,
                            'sale_1_completed' => 0,
                            'sale_2_completed' => 0,
                            'sale_1_triggered' => 0,
                            'sale_2_triggered' => 0,
                        ];
                        $coinscheme->scheme->coins()->updateExistingPivot($coinscheme->coin->id, $attributes);

                    }
                }

                //message response
                if($volume) $json['success'] = $volume." ".$coinscheme->coin->code." sold";
                else $json['danger'] = "Sale Failed";
                break;
        }

        return json_encode($json);
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
