<?php
/** Maybe have a separate repository file for kraken and bittrex **/

namespace App\Repositories;

use App\Coin;
use App\CoinPrice;
use App\Transaction;
use App\Scheme;
use App\User;
use App\Notifications\Trade;
use adman9000\kraken\KrakenAPIFacade;
use adman9000\Bittrex\Bittrex;
use Illuminate\Support\Facades\File;

class Exchanges {


    /* runSchedule()
     * Called by cronjob every 5 minutes.
     * Runs the required functions in order to
     * 1. Save latest prices from bittrex to DB
     * 2. Run the trading rules to buy & sell as required
     * 3. Push Latest Coin prices & amounts via pusher
     * 4. Push latest BTC price & amount via pusher
     **/
    function runSchedule() {

        $this->saveBittrexPrices();
        $this->checkForCompletedOrders();
        $this->runTradingRules();
        $this->coinPusher();
        $this->btcPusher();

    }

    /** btcPusher
    * TODO: store BTC rate & amount in DB?
     * Push BTC info
     */
    function btcPusher() {

        $data = array();

        //Get BTC balance & exchange rate from bittrex
        $btc_balance = Bittrex::getBalance("BTC");
        $btc_market = Bittrex::getMarketSummary("USDT-BTC");
        $data['btc_additional_amount'] = $btc_balance['result']['Balance'];
        $data['btc_usd_rate'] = $btc_market['result'][0]['Last'];

        //Pusher
        broadcast(new \App\Events\PusherEvent(json_encode($data), "portfolio\\btc"));
    }

      /**coinPusher
    * Called from /coins page to update it on load
    * And every 5 minutes to update the page after new prices obtained & trades carried out
    **/
    function coinPusher() {

        $log_file = storage_path("logs/bittrex.log");
        File::append($log_file, "--------------------------------- ".date("d/m/Y G:i")."----------------------------------"."\n");
        File::append($log_file, "--------------------------------- coinPusher() ----------------------------------"."\n");

        //Get an array of all coins in my DB
       // $coins = Coin::all();

        //Loop through all live schemes and broadcast current prices and amounts of all coins held
        $schemes = Scheme::where("enabled", true)->get();

        foreach($schemes as $scheme) {


            File::append($log_file, "\nScheme pushing: ".$scheme->title."\n");

            //Add extra info to coins
            $data = array();

            foreach($scheme->coins as $c=>$coin) {
                $datum = array();
                $datum['id'] = $coin->id;
                $datum['code'] = $coin->code;
                $datum['name'] = $coin->name;
                $datum['current_price'] = round($coin->latestCoinPrice->current_price, 7);
                $datum['current_value'] = round($coin->pivot->amount_held * $coin->latestCoinPrice->current_price, 7);
                $datum['diff'] = round((($coin->latestCoinPrice->current_price / $coin->pivot->set_price) * 100) - 100, 2);
                $datum['set_price'] = round($coin->pivot->set_price, 7);
                $datum['been_bought'] = $coin->pivot->been_bought;
                $datum['sale_completed_1'] = $coin->pivot->sale_completed_1;
                $data[] = $datum;
            }

            broadcast(new \App\Events\PusherEvent(json_encode($data), "portfolio\\prices\\".$scheme->id));
        }

    }


    /** getBittrexPrices
    ** called every 5 mins, saves bittrex prices to DB
    **/
    function saveBittrexPrices() {


        $log_file = storage_path("logs/bittrex.log");
        File::append($log_file, "--------------------------------- ".date("d/m/Y G:i")."----------------------------------"."\n");
        File::append($log_file, "--------------------------------- saveBittrexPrices() ----------------------------------"."\n");

        //Get latest markets for everythign on bittrex
        $markets = Bittrex::getMarketSummaries();

        //Get an array of all coins in my DB
        $coins = Coin::all();

        //Loop through markets, find any of my coins and save the latest price to DB
        foreach($markets['result'] as $market) {
            $arr = explode("-", $market['MarketName']);
            $base = $arr[0];
            if($base == "BTC") {
                foreach($coins as $coin) {
                    if($coin->code == $arr[1]) {
                        $price_info = array("coin_id"=>$coin->id, "current_price"=>$market['Last']);
                        $price = CoinPrice::create($price_info);

                        File::append($log_file, "Price saved for ".$coin->code);
                    }
                }
            }
        }

    }

    /**updateCoinBalances
     * Save current bittrex balances to DB
     * Not needed that often? Trade function does this anyway
    **/
    function updateCoinBalances() {
    
        //Get balances of my coins according to Bittrex
        $balances = Bittrex::getBalances();

        //Get an array of all coins in my DB
        $coins = Coin::all();

        //For pusher event
        $latest_prices = array();

        //Put balances into coins array and save balance to DB
        $my_balances = array();
        foreach($balances['result'] as $balance) {
            foreach($coins as $c=>$coin) {
                if($coin->code == $balance['Currency']) {
                    $coins[$c]->amount_owned= $balance['Balance'];
                    $coins[$c]->save();
                }
            }
        }
    }


  

    //reset coins to current price, no triggers, only used on setup
    function resetCoins() {

        Coin::where('id', '>', 0)->update([
            'been_bought' => 0,
            'sale_completed_1' => 0,
            'sale_completed_2' => 0,
            'sale_trigger_1' => 0,
            'sale_trigger_2' => 0,
            'highest_price' => 0
            ]);

        $coins = Coin::with('latestCoinprice')->get();
        foreach($coins as $coin) {
            $coin->buy_point = $coin->latestCoinprice->current_price;
            $coin->highest_price = $coin->latestCoinprice->current_price;
            $coin->save();
        }
    }


    /*  runTradingRules
     * Important function! Does the buying & selling
    * Runs every 5 minutes after we have got latest prices
    */
    function runTradingRules() {

        $log_file = storage_path("logs/bittrex.log");
        File::append($log_file, "--------------------------------- ".date("d/m/Y G:i")."----------------------------------"."\n");
        File::append($log_file, "--------------------------------- runTradingRules() ----------------------------------"."\n");

        if(!env("AUTOTRADE_ENABLED")) {

            File::append($log_file, "Autotrade disabled\n"."\n");
            return false;
        }

        //Get any existing orders first so we dont duplicate - TODO: Cancel existing orders if not filled?
        $orders = Bittrex::getOpenOrders();
        foreach($orders['result'] as $order) {
            $arr = explode("-", $order['Exchange']);
            $existing_orders[] = $arr[1];
        }

        //Get all DB coins with latest prices
       // $coins = Coin::with('latestCoinprice')->get();


       // $balances = Bittrex::getBalances();

        //Get all enabled schemes
        $schemes = scheme::where("enabled", true)->get();

        foreach($schemes as $scheme) {

            File::append($log_file, "\nScheme Running: ".$scheme->title."\n");

            foreach($scheme->coins as $coin) {

                File::append($log_file, "\n".$coin->code."\n");

                //Probably a better way to do this with relationships??
                $mycoin = Coin::with('latestCoinprice')->find($coin->id);
                $latest_price = $mycoin->latestCoinprice;

                $baseline_price = $coin->pivot->set_price;

                $next_baseline_price = $scheme->price_increase_percent ?  $baseline_price * $scheme->price_increase_percent/100 : $baseline_price;

                $current_price = $latest_price->current_price;

                $amount_held = $coin->pivot->amount_held;

                $highest_price = max($current_price, $coin->highest_price);

                $buy_point = $baseline_price - ($baseline_price*$scheme->buy_drop_percent/100);

                $btc_buy_amount = $scheme->buy_amount;

                $coin_buy_amount = $scheme->buy_amount / $current_price;

                $sale_1_trigger = $baseline_price + ($baseline_price*$scheme->sell_1_gain_percent/100);

                $sale_1_point = $scheme->sell_1_drop_percent==0 ? false : $highest_price - ($highest_price*$scheme->sell_1_drop_percent/100);

                $sale_2_trigger = $baseline_price + ($baseline_price*$scheme->sell_2_gain_percent/100);

                $sale_2_point = $scheme->sell_2_drop_percent==0 ? false : $highest_price - ($highest_price*$scheme->sell_2_drop_percent/100);

                $sale_1_amount = $amount_held*$scheme->sell_1_sell_percent/100;

                $sale_2_amount = $amount_held*$scheme->sell_2_sell_percent/100;

                File::append($log_file, "Baseline Price: ".$baseline_price."\n");
                File::append($log_file, "Next Baseline Price: ".$next_baseline_price."\n");
                File::append($log_file, "Current Price: ".$current_price."\n");
                File::append($log_file, "High Price: ".$highest_price."\n");
                File::append($log_file, "Buy Point: ".$buy_point."\n");
                File::append($log_file, "Coin Buy Amount: ".$coin_buy_amount."\n");
                File::append($log_file, "Sale 1 Trigger: ".$sale_1_trigger."\n");
                File::append($log_file, "Sale 1 Price: ".$sale_1_point."\n");
                File::append($log_file, "Sale 2 Trigger: ".$sale_2_trigger."\n");
                File::append($log_file, "Sale 2 Price: ".$sale_2_point."\n");
                File::append($log_file, "Amount Held: ".$amount_held."\n");
                File::append($log_file, "Sale 1 Amount: ".$sale_1_amount."\n");
                File::append($log_file, "Sale 2 Amount: ".$sale_2_amount."\n");

                if($coin->pivot->been_bought) {
                    
                    //We own some of this coin in this scheme, so deal with it!
                   
                    File::append($log_file, "Testing Sell Points:" .$coin->pivot->sale_1_completed."\n");

                    if($coin->pivot->sale_1_completed==0) {

                        File::append($log_file, "Sale 1 not completed"."\n");

                        //Handle sale 1 level
                        if($current_price >= $sale_1_trigger) {

                            //Current price has reached trigger point 1
                            $coin->pivot->sale_1_triggered = true;

                            File::append($log_file, "Sale 1 triggered"."\n");

                        }

                        if($coin->pivot->sale_1_triggered) {

                            File::append($log_file, "Sale 1 already triggered"."\n");

                            //We have reached the trigger point for sale 1. Sell once we reach the sell point

                            if(!($coin->pivot->sale_1_completed) && ((!$sale_1_point) || ($current_price <= $sale_1_point))) {

                                //SELL SELL SELL!
                                File::append($log_file, "SALE 1 - SELLING ".$sale_1_amount."\n");

                                 if($this->bittrexSell($coin, $sale_1_amount, $current_price, $scheme)) {

                                    //TODO: Move all this to the order checking function so we only mark coins as bought etc when order has been confirmed?
                                    //Set sale_completed=1
                                    $coin->pivot->sale_1_completed = true;
                                    $coin->pivot->amount_held -= $sale_1_amount;

                                    File::append($log_file, "Sale successful"."\n");

                                    //If sale 1 is for 100%, reset triggers
                                    if($coin->pivot->amount_held == 0) {
                                        //Reset triggers, update baseline price
                                        $coin->pivot->been_bought = false;
                                        $coin->pivot->sale_1_triggered = false;
                                        $coin->pivot->sale_1_completed = false;
                                        $coin->pivot->sale_2_triggered = false;
                                        $coin->pivot->set_price = $next_baseline_price;
                                        $coin->pivot->amount_held -= $sale_2_amount;
                                        $highest_price = 0;

                                        File::append($log_file, "Sold out. Triggers reset"."\n");

                                    }


                                }


                            }

                            //Current price not yet dropped enough to sell

                        }

                    }
                    
                    if(!$coin->pivot->sale_2_completed) {

                        File::append($log_file, "Sale 2 not completed"."\n");

                        //Handle sale 2 level
                        if($current_price >= $sale_2_trigger) {

                            $coin->pivot->sale_2_triggered = true;

                            File::append($log_file, "Sale 2 triggered"."\n");

                        }

                        if($coin->pivot->sale_2_triggered) {

                            //We have reached the trigger point for sale 1. Sell once we reach the sell point

                            if((!$sale_2_point) || ($current_price <= $sale_2_point)) {

                                //SELL SELL SELL!

                                File::append($log_file, "SALE 2 - SELLING ".$sale_2_amount);

                                 if($this->bittrexSell($coin, $sale_2_amount, $current_price, $scheme)) {

                                    //Reset triggers, update baseline price
                                    $coin->pivot->been_bought = false;
                                    $coin->pivot->sale_1_triggered = false;
                                    $coin->pivot->sale_1_completed = false;
                                    $coin->pivot->sale_2_triggered = false;
                                    $coin->pivot->set_price = $next_baseline_price;
                                    $coin->pivot->amount_held -= $sale_2_amount;
                                    $highest_price = 0;

                                    File::append($log_file, "Sale successful"."\n");

                                }

                            }

                            //Current price not yet dropped enough to sell

                        }
                    }
                }

                else {

                    //Handle buying the coin

                    File::append($log_file, "Testing Buy Point"."\n");

                    if($current_price <= $buy_point) {

                        //TODO: Add trigger percent to avoid buying during crash

                        //Buy it
                        File::append($log_file, "BUYING ".$coin_buy_amount." with ".$btc_buy_amount." BTC"."\n");

                        if( $volume = $this->bittrexBuy($coin, $btc_buy_amount, $current_price, $scheme)) {

                            //St the bought flag
                            $coin->pivot->been_bought = 1;

                            $coin->pivot->amount_held += $volume;
                            
                            File::append($log_file, "Buy successful"."\n");

                        }

                    }

                }


                 File::append($log_file, "Amount Held: ".$coin->pivot->amount_held."\n");
                //Save the coin_scheme data
                $coin->pivot->highest_price = $highest_price;
                $coin->pivot->save();

            } //end foreach coin
 


        }

        
    }


    /** checkIncompleteOrders
    * Check incomplete transactions against orders in bittrex to mark the transactions as complete
    */
    function checkForCompletedOrders() {
     //Get all incomplete transactions and check against orders on bittrex to see if they have been completed
        $transactions = Transaction::where("status", "unconfirmed")->where('uuid', '!=', '')->get();
        foreach($transactions as $transaction) {
            $order = Bittrex::getOrder($transaction->uuid);
            if($order['result']['Closed'] == true) {
                $transaction->amount_bought = $order['result']['Quantity'];
                $transaction->amount_sold = $order['result']['Price'];
                $transaction->exchange_rate = $order['result']['PricePerUnit'];
                $transaction->fees = $order['result']['CommisionPaid'];
                $transaction->status='confirmed';
                $transaction->save();
            }
        }
    }



    /** Buy & Sell functions
     * Called from the Trade function when required
    **/

    /** bittrexSell()
     * @param $coin - the coin being bought
     * @param $volume - the amount of that coin being sold (for BTC)
     * @param $rate - the limiting rate of exchange
     **/
    function bittrexSell($coin, $volume, $rate, $scheme=false) {

        if($scheme) $scheme_id = $scheme->id;

        if(env("AUTOTRADE_ENABLED") == "test") {
            $order['success'] =  true;//TESTING
            $order['result']['uuid'] = "TEST0001";
            //return true;
        }
        else {
            //Place order
            $order = Bittrex::sellLimit("BTC-".$coin->code, $volume, $rate);
        }

        if(!$order['success']) {
            //Order failed, alert me somehow


        $user = User::find(1);
        $user->notify(new Trade(array("type" => "sell", "coin" => $coin, "order" => $order, "scheme" => $scheme, "transaction" => array(), "success" => false)));

            return false;
        }
        else {

             //Order successful, save transaction to DB
            $transaction_info = array(
                "coin_bought_id" => 0,
                "coin_sold_id" => $coin->id,
                "amount_sold" => $volume,
                "amount_bought" => $volume*$rate,
                "exchange_rate" => $rate,
                'fees' => 0,
                'user_id' => 1,
                "status" => "unconfirmed",
                "uuid" => $order['result']['uuid'],
                'scheme_id' => $scheme_id
                );
            $transaction = Transaction::create($transaction_info);


        $user = User::find(1);
        $user->notify(new Trade(array("type" => "sell", "coin" => $coin, "order" => $order, "scheme" => $scheme, "transaction" => $transaction, "success" => true)));

            return $volume;
        }
    }

    /** bittrexBuy()
     * @param $coin - the coin being bought
     * @param $amount - the amount of BTC being spent
     * @param $rate - the limiting rate of exchange
     **/
    function bittrexBuy($coin, $amount, $rate, $scheme=false) {
        
        if($scheme) $scheme_id = $scheme->id;

        $log_file = storage_path("logs/bittrex.log");
         //Calculate volume being bought from the amount of BTC being sold, the rate, and bittrex fees (0.25%)
        $volume = ($amount - ($amount*0.0025))/$rate; 

			File::append($log_file, "AUTOTRADE_ENABLED: ".env("AUTOTRADE_ENABLED"));
			
         if(env("AUTOTRADE_ENABLED") == "test") {
            $order['success'] =  true;//TESTING
            $order['result']['uuid'] = "TEST0001";
        }
        else {
            
            //Place order
            $order = Bittrex::buyLimit("BTC-".$coin->code, $volume, $rate);
        }

			File::append($log_file, json_encode($order));
			
        if(!$order['success']) {
            //Order failed, alert me somehow
	
            $user = User::find(1);
            $user->notify(new Trade(array("type" => "buy", "coin" => $coin, "order" => $order, "scheme" => $scheme, "transaction" => array(), "success" => false)));

            return false;
        }
        else {
             //Order successful, save transaction to DB
            $transaction_info = array(
                "coin_sold_id" => 0,
                "coin_bought_id" => $coin->id,
                "amount_sold" => $amount,
                "amount_bought" => $volume,
                "exchange_rate" => $rate,
                'fees' => 0,
                'user_id' => 1,
                "status" => "unconfirmed",
                "uuid" => $order['result']['uuid'],
                'scheme_id' => $scheme_id
                );
            $transaction = Transaction::create($transaction_info);


            $user = User::find(1);
            $user->notify(new Trade(array("type" => "buy", "coin" => $coin, "order" => $order, "scheme" => $scheme, "transaction" => $transaction, "success" => true)));
        
            return $volume;
        }
    }






    /********* V1 ***/
	 /** getPrices
    }
    * Get latest prices for all my coins from relevant exchanges
    **/

	public function getPrices() {

		//First of all we can skip Euros (might take this out of coins list entirely)

        //Secondly, bitcoins are handled differently. All other coins are priced in bitcoin at whichever exchange they were bought from, bitcoin is priced in Euro based on Kraken exchange

        $coins = Coin::where("code", "!=", "EUR")->where("code", "!=", "XBT")->get();

        $xbt = Coin::where("code", "=", "XBT")->first();

        //Create the array of asset pairs to pass to the kraken ticker
        $kraken_pairs = array();
        $bittrex_coins = array();

        //Preset first kraken pair to XBTEUR
        $kraken_pairs[] = "XBTEUR";
        foreach($coins as $coin) {
            if($coin->exchange == "kraken") $kraken_pairs[] = $coin->code."XBT";
            else if($coin->exchange == "bittrex") $bittrex_coins[] = $coin;
        }

        //KRAKEN VERSION - Gets all tickers with one call

        //Get Ticker prices
        $ticker = KrakenAPIFacade::getTickers($kraken_pairs);

        if(isset($ticker['result'])) {

            //Save the bitcoin one
            if(isset($ticker['result']['XXBTZEUR'])) {
                $price = new CoinPrice();
                $price->coin_id = $coin->id;
                $price->current_price = $ticker['result']['XXBTZEUR']['a'][0];
                $price->save();
                $price->coin_code = "XBT";
                $latest_prices[] = $price;
                unset($ticker['result']['XXBTZEUR']);
            }

            //Figure out the rest, kraken has weird codes!
            foreach($ticker['result'] as $pair=>$values) {
                if(substr($pair, strlen($pair)-4, 4) == "XXBT") $code = substr($pair, 1, 3);
                else $code = substr($pair, 0, 4);

                //Get the coin id and save the price
                $coin = Coin::where("code", "=", $code)->first();
                $price = new CoinPrice();
                $price->coin_id = $coin->id;
                $price->current_price = $values['a'][0];
                $price->save();
                $price->coin_code = $coin->code;
                $latest_prices[] = $price;
            }
        }

        //BITTREX VERSION - does tickers singly

            //Get latest price from bittrex (BITCOIN)
            foreach($bittrex_coins as $coin) {
                $info = Bittrex::getTicker("BTC-".$coin->code);
                if((is_array($info)) && (isset($info['result']))) $latest = $info['result']['Last'];
            
                //Save the price
                if($latest) {
                    $price = new CoinPrice();
                    $price->coin_id = $coin->id;
                    $price->current_price = $latest;
                    $price->save();
                    $price->coin_code = $coin->code;
                    $latest_prices[] = $price;
                }
            }


        //send pusher event informing of latest coin prices
        $data = array();
        foreach($latest_prices as $price) {
            $data[$price->coin_code] = array();
            $data[$price->coin_code]['price'] = $price->current_price;
            $data[$price->coin_code]['updated_at'] = $price->created_at;
            $data[$price->coin_code]['updated_at_short'] = $price->created_at->format('D G:i');
        }
        broadcast(new \App\Events\PusherEvent(json_encode($data)));

    }

}