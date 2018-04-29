<?php

namespace App\Http\Controllers\Dashboard;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Modules\Portfolio\Exchange;
use App\Modules\Portfolio\UserExchange;
use App\Modules\Portfolio\Coin;
use App\Modules\Portfolio\CoinPrice;
use App\Modules\Portfolio\UserCoin;
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


      //Get all our account stats from the different exchanges we use
       $data = array();
       $data['btc_value'] = 0;
       $data['usd_value'] = 0;
       $data['gbp_value'] = 0;
       $data['stats'] = array();

       $user = Auth::user();

        //Load all this users coins
        $user->load('coins');

        //Load all the coins for each of their exchanges
        foreach($user->exchanges as $exchange) {
            $exchange->exchange->load('coins');

            $exchangedata = array('btc_value'=>0,'usd_value'=>0,'gbp_value'=>0);
            //Loop through users coins & calculate the current value of each in GBP & USD
            foreach($exchange->exchange->coins as $ecoin) {
                foreach($user->coins as $ucoin) {
                    if($ecoin->id == $ucoin->exchange_coin_id) {
                        $exchangedata['btc_value'] += $ucoin->balance * $ecoin->btc_price;
                        $exchangedata['usd_value'] += $ucoin->balance * $ecoin->usd_price;
                        $exchangedata['gbp_value'] += $ucoin->balance * $ecoin->gbp_price;
                    }
                }
                $data['stats'][$exchange->exchange->slug] = $exchangedata;

            }

        }

        //Format the currency values
       // $data['usd_value'] = number_format($data['usd_value'], 2);
       // $data['gbp_value'] = number_format($data['gbp_value'], 2);

        return view('dashboard.exchanges.index', $data);
    
    }


    /** show()
     * @param $name - name of exchange to display
     * @return view
    **/
    public function show($name, Request $request) {



      $data = array();
      $data['stats'] = array();
      $data['stats']['assets'] = array();
      $data['stats']['btc']['balance'] = 0;

      $exchange = Exchange::where("slug", "=", $name)->first();

      $user = Auth::user();

      $user_exchange = UserExchange::where("user_id", "=", $user->id)->where("exchange_id", "=", $exchange->id)->first();
	  
      //Load all this users coins
        $user->load('coins');

        $exchange->load('coins');

        $data['exchange'] = $exchange;
		$data['user_exchange'] = $user_exchange;

        foreach($exchange->coins as $ecoin) {

          $asset = $ecoin;

        foreach($user->coins as $ucoin) {

            if($ucoin->exchange_coin_id == $ecoin->id) {

              $asset->user_coin_id = $ucoin->id;
              $asset->balance = $ucoin->balance;
              $asset->available = $ucoin->available;
              $asset->locked = $ucoin->locked;
              $asset->btc_value = $asset->balance * $asset->btc_price;
              $asset->gbp_value = $asset->balance * $asset->gbp_price;
              $asset->usd_value = $asset->balance * $asset->usd_price;       

              if($ecoin->code == "BTC") { //prob wont work on kraken
                $data['stats']['btc']['balance'] = $ucoin->balance;

              }

            }
          }
          $data['stats']['assets'][] = $asset;
      }

      return view("dashboard.exchanges.show", $data);
    }


    /** actions()
    *
    * All extra actions - buying, selling etc
    */
    public function actions($name=false,  Request $request) {

      $exchange = Exchange::where("slug", "=", $name)->first();
      
      $user = Auth::user();

      switch(request('action')) {

        case "resync" :
          $user_exchange = UserExchange::where("exchange_id", $exchange->id)->where("user_id", $user->id)->first();
           $user_exchange->updateBalances();
          break;

        case "sell" :
          $usercoin = UserCoin::find(request('user_coin_id'));
          $usercoin->marketSell(request('volume'));

        break;

          case "buy" :
          $usercoin = UserCoin::find(request('user_coin_id'));
		  
		  if(!$usercoin) {
			$ucoin_data = $request->all();
			$ucoin_data['user_id'] = $user->id;
			$ucoin_data['balance'] = 0;
			$ucoin_data['available'] = 0;
			$ucoin_data['locked'] = 0;
			$ucoin_data['gbp_value'] = 0;
			$usercoin = UserCoin::create($ucoin_data);
			
			}
          $usercoin->marketBuy(request('volume'));

        break;
      }

      return $this->show($name, $request);
    }


    //Display the deposit address for this asset
    public function getAssetAddress(Exchange $exchange, Coin $coin) {

      $user = Auth::user();

      $user_exchange = UserExchange::where("user_id", "=", $user->id)->where("exchange_id", "=", $exchange->id)->first();

      $data['coin'] = $coin;
      $data['exchange'] = $exchange;
      $data['address'] = $user_exchange->getAssetAddress($coin->code);

      return view('dashboard.exchanges.ajax.address', $data);
    }


    //////////Maybe not used any more


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
    **

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
     *
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

**/


}
