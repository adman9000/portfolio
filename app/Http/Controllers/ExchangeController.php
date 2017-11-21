<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Modules\Portfolio\Exchange;
use App\Modules\Portfolio\Coin;
use App\Modules\Portfolio\CoinPrice;
use adman9000\kraken\KrakenAPIFacade;
use adman9000\Bittrex\Bittrex;
use App\Repositories\Exchanges;
use Illuminate\Support\Facades\Auth;

class ExchangeController extends Controller
{
    public function __construct() {
         $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {


        //TODO: Combine exhanges in DB with APIs somehow?

       $user = Auth::user();

       $data = array();
       $data['stats'] = array();
       $data['stats']['total'] = array();
       $data['stats']['total']['btc_value'] = 0;
       $data['stats']['total']['usd_value'] = 0;


       foreach($user->exchanges as $exchange) {

            $data['stats'][$exchange->exchange->slug] = $exchange->getAccountStats();
            $data['stats']['total']['btc_value'] += $data['stats'][$exchange->exchange->slug]['total_btc_value'];
            $data['stats']['total']['usd_value'] += $data['stats'][$exchange->exchange->slug]['total_usd_value'];

        }

        //Add the GBP value to the data array
        $data['usd_gbp_rate']  = env("USD_GBP_RATE");
        $data['btc_value'] = $data['stats']['total']['btc_value'];
        $data['usd_value'] = number_format($data['stats']['total']['usd_value'], 2);
        $data['gbp_value'] = number_format(($data['stats']['total']['usd_value'] / $data['usd_gbp_rate']), 2);

        return view('exchanges.index', $data);
    
    }


    public function show($name, Request $request) {

      $data = array();
      $data['exchange'] = Exchange::where("slug", "=", $name)->first();

      $user = Auth::user();

      //get the user exchange model
      $user_exchange = $user->exchanges->where("exchange_id", "=", $data['exchange']->id)->first();

      $data['stats'] = $user_exchange->getBalances(true);

      return view("exchanges.show", $data);
    }

    /** getPrices
    * Get latest prices for all my coins from relevant exchanges
    **/
    public function getPrices(Exchanges $exchanges) {

        $exchanges->updateCoinBalances();
        $exchanges->saveBittrexPrices();


    }

    //Manually run the trading rules - not needed if cronjob is working
  public function runTradingRules(Exchanges $exchanges) {

      $exchanges->runTradingRules();

    }

    //Send coin info to pusher to be picked up by page
  public function coinPusher(Exchanges $exchanges) {

      $exchanges->btcPusher();
      $exchanges->coinPusher();

    }

    //Shouldnt be needed now live
    public function resetCoins(Exchanges $exchanges) {

      // $exchanges->resetCoins();

    }

    //TODO: Add view for open orders with buttons to close them
    public function getOpenOrders() {

      $orders = Bittrex::getOpenOrders();

      dd($orders);
    }

    /** OLD VERSION 
    * Functions for showing bittrex & kraken pages
    **/

    public function kraken()
    {

        $data = array();
        $data['exchange'] = "Kraken";
        
        $post = request()->all();


        if(isset($post['action'])) {


            switch($post['action'])  {

                case "sell" :

                    $order = KrakenAPIFacade::sellMarket(array($post['coin_1'], $post['coin_2']), $post['volume']);

                    if(sizeof($order['error'])>0) {
                            $data['order_error'] = $order['error'][0];
                        }
                        else {
                             $data['order_description'] = $order['result']['descr']['order'];
                             $data['order_txid'] = $order['result']['txid'][0];
                        }


                    break;

                case "buy" :

                    //Get latest price from kraken
                    $info = KrakenAPIFacade::getTicker(array($post['coin_1'], $post['coin_2'])); 
                    if((isset($info['result'])) && (is_array($info['result']))) {
                        $result = reset($info['result']);
                        $latest = $result['a'][0];
                        $volume = $post['volume'] / $latest;

                        $order = KrakenAPIFacade::buyMarket(array($post['coin_1'], $post['coin_2']), $volume);

                        if(sizeof($order['error'])>0) {
                            $data['order_error'] = $order['error'][0];
                        }
                        else {
                             $data['order_description'] = $order['result']['descr']['order'];
                             $data['order_txid'] = $order['result']['txid'][0];
                        }
                    }

                    break;
            }

        }

        //List all exchanges

        //For now just show balances on Kraken
        //$assets = KrakenAPIFacade::getAssetInfo();
        $balances = KrakenAPIFacade::getBalances();

        $data['balances'] = $balances['result'];

        return view("exchanges.show", $data);
    }

 /**
     * Show Bittrex
     *
     * @return \Illuminate\Http\Response
     */
    public function bittrex()
    {
        //create data array
        $data = array();
        $data['exchange'] = "Bittrex";

        $post = request()->all();
         if(isset($post['action'])) {


            switch($post['action'])  {

                case "sell" :

                    $ticker = Bittrex::getTicker("BTC-".$post['coin_1']);

                    $rate = $ticker['result']['Last'];

                    $order = Bittrex::sellLimit("BTC-".$post['coin_1'], $post['volume'], $rate);

                        if(!$order['success']) {
                            $data['order_error'] = $order['message'];
                        }
                        else {
                             $data['order_description'] = "Order Successful";
                             $data['order_txid'] = $order['result']['uuid'];
                        }

                    break;

                case "buy" :


                    //Get latest price from Bittrex
                     $ticker = Bittrex::getTicker("BTC-".$post['coin_1']);

                     $rate = $ticker['result']['Last'];
                     $volume = ($post['volume'] - 0.0001)/$rate;

                     $order = Bittrex::buyLimit("BTC-".$post['coin_1'], $volume, $rate);


                        if(!$order['success']) {
                            $data['order_error'] = $order['message'];
                        }
                        else {
                             $data['order_description'] = "Order Successful";
                             $data['order_txid'] = $order['result']['uuid'];
                        }

                    break;
            }

        }

        


        $balances = Bittrex::getBalances();
        foreach($balances['result'] as $balance) {
            $data['balances'][$balance['Currency']] = $balance['Balance'];
        }

        return view("exchanges.show", $data);
    }




}
