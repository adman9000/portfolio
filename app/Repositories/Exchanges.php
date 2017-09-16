<?php
/** Maybe have a separate repository file for kraken and bittrex **/

namespace App\Repositories;

use App\Coin;
use App\CoinPrice;
use App\Transaction;
use adman9000\kraken\KrakenAPIFacade;
use adman9000\Bittrex\Bittrex;
use Illuminate\Support\Facades\File;

class Exchanges {


    function runSchedule() {

        $this->getBittrexPrices();
        $this->runTradingRules();

    }

    /** getBittrexPrices
    **/

    function getBittrexPrices() {

        //Set current prices for my coins

        $markets = Bittrex::getMarketSummaries();
        $coins = Coin::all();

        //For pusher event
        $latest_prices = array();

        foreach($markets['result'] as $market) {
            $arr = explode("-", $market['MarketName']);
            $base = $arr[0];
            if($base == "BTC") {
                foreach($coins as $coin) {
                    if($coin->code == $arr[1]) {
                        $price_info = array("coin_id"=>$coin->id, "current_price"=>$market['Last']);
                        $price = CoinPrice::create($price_info);
                        //For pusher event
                        $price->coin_code = $coin->code;
                        $latest_prices[] = $price;
                    }
                }
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
        broadcast(new \App\Events\PusherEvent(json_encode($data), "portfolio\\prices"));
        
    }

    //reset coins to current price, no triggers
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

    /* Should run after we have got latest prices
    */
    function runTradingRules() {

        $log_file = storage_path("logs/bittrex.log");


        File::append($log_file, "--------------------------------- ".date("d/m/Y G:i")."----------------------------------"."\n");

        if(!env("AUTOTRADE_ENABLED")) {

            File::append($log_file, "Autotrade disabled\n"."\n");
            return false;
        }

        //Get any existing orders first so we dont duplicate
        $orders = Bittrex::getOpenOrders();
        foreach($orders->result as $order) {
            $arr = explode("-", $order['Exchange']);
            $existing_orders[] = $arr[1];
        }

        //Amount of BTC to spend when buying
        $btc_buy_amount = env('BTC_BUY_AMOUNT');
        $sell_point_1_multiplier = env('SELL_POINT_1');
        $sell_point_2_multiplier = env('SELL_POINT_2');
        $sell_drop_2_percentage = env('SELL_DROP_2');

        //used by pusher
        $data = array();

        $coins = Coin::with('latestCoinprice')->get();

        $balances = Bittrex::getBalances();

        $my_balances = array();
        foreach($balances['result'] as $balance) {
            $my_balances[$balance['Currency']] = $balance['Balance'];
        }

        foreach($coins as $coin) {


            File::append($log_file, $coin->code."\n");

            if(in_array($coin->code, $existing_orders)) {

                 File::append($log_file, "Order already exists\n");
                 continue;
            }


            //orderok flag
            $order_ok = true;

            //Get current price
            $current_price = $coin->latestCoinPrice->current_price;

            //Get current balance
            $current_balance =  isset($my_balances[$coin->code]) ? $my_balances[$coin->code] : 0;


            File::append($log_file, "Current Price: ".$current_price."\n");
            File::append($log_file, "Current Balance: ".$current_balance."\n");

            //Only attempt to sell if balance > 0
            if($current_balance > 0) {

                //Always update highest price point if current price is higher
                $coin->highest_price = max($coin->highest_price, $current_price);

                 File::append($log_file, "Highest Price: ".$coin->highest_price."\n");

                //Those with a current price at least that of $sale_point_1 sell 50% for BTC

                //Calculate the first sell point
                $sell_point_1 = $coin->buy_point * $sell_point_1_multiplier;

                File::append($log_file, "Sell Point 1: ".$sell_point_1."\n");

                if($coin->sale_completed_1) File::append($log_file, "Sale 1 previously completed\n");
                
                if(($current_price >= $sell_point_1) && (!$coin->sale_completed_1)) {
                   
                    //Sell first 50%
                    File::append($log_file, "Selling first 50%"."\n");

                    if($this->bittrexSell($coin, $current_balance/2, $current_price)) {

                        //Set sale_completed=1
                         $coin->sale_completed_1 = true;
                         $current_balance = $current_balance/2;
                         $data['sales'][] = "50% ".$coin->code." Sold";

                        File::append($log_file, "Sale successful"."\n");

                    }
                }


                //Those with a current price at least that of $sale_point_2 set the $sale_trigger_2 variable and record current price in $price_high if higher than current $price_high
                
                //Calculate second sell point
                $sell_trigger_2 = $coin->buy_point * $sell_point_2_multiplier;
                $sell_point_2 = $coin->highest_price - (( $coin->highest_price*$sell_drop_2_percentage)/100);
                

                 File::append($log_file, "Sell Trigger 2: ".$sell_trigger_2."\n");
                 File::append($log_file, "Sell Point 2: ".$sell_point_2."\n");

                if($current_price >= $sell_trigger_2) {

                    if(!$coin->sale_trigger_2) {
                        //Set trigger to sell when price drops 5%
                        $coin->sale_trigger_2 = 1;

                        File::append($log_file, "Trigger Set"."\n");

                    }
                    else {
                         //Those with $sale_trigger_2 set, check if current price is 5% lower than $price_high. If so sell remaining stock and double the buy in price.
                        //Check for 5% price drop from highest point
                        if($current_price <= $sell_point_2) {

                            //Sell remainder
                                File::append($log_file, "Selling Remainder"."\n");
                            if($this->bittrexSell($coin, $current_balance, $current_price)) {

                                //Reset triggers, double buy in price
                                $coin->sale_trigger_2 = 0;
                                $coin->sale_completed_1 = 0;
                                $coin->buy_point = $coin->buy_point * $sell_point_1_multiplier;
                                $current_balance = 0;
                                $data['sales'][] = "Remaining ".$coin->code." Sold";

                                File::append($log_file, "Sale successful"."\n");

                            }
                        }
                    }
                }

            }
            else {
                $coin->been_bought=0; //make sure coins with no balance are set to 'not bought'

                File::append($log_file, "Zero Balance Detected"."\n");
            }


            File::append($log_file, "Buy Point: ".$coin->buy_point."\n");

            File::append($log_file, "Been Bought: ".$coin->been_bought."\n");

            //Coins that have dropped below the buy point and have not yet been bought, can be bought

            if( ($current_price <= $coin->buy_point) && (!$coin->been_bought) ) {


                File::append($log_file, "Buying full amount"."\n");

                if( $volume = $this->bittrexBuy($coin, $btc_buy_amount, $current_price)) {

                    //St the bought flag
                    $coin->been_bought = 1;

                    $current_balance += $volume;
                    
                    $data['sales'][] = "Full amount of ".$coin->code." Bought";

                    File::append($log_file, "Buy successful"."\n");

                }
            }

            //Coins that have dropped below the buy point and have had 50% sold can be bought again (50%)
             if( ($current_price <= $coin->buy_point) && ($coin->been_bought) && ($coin->sale_completed_1) ) {

                File::append($log_file, "Buying 50%"."\n");

                if( $volume = $this->bittrexBuy($coin, $btc_buy_amount/2, $current_price)) {

                     //unset the sale_completed_1 flag
                    $coin->sale_completed_1 = 0;

                    $current_balance += $volume;

                    $data['sales'][] = "50% ".$coin->code." Bought";

                    File::append($log_file, "Buy successful"."\n");
                }
            }

            //Save updated coin details
            $coin->save();

            File::append($log_file, "Coin Saved"."\n"."\n");

            //For pusher
            $coin_data = array();
            $coin_data['been_bought'] = $coin->been_bought;
            $coin_data['sale_trigger_2'] = $coin->sale_trigger_2;
            $coin_data['sale_trigger_1'] = $coin->sale_trigger_1;
            $coin_data['sale_completed_1'] = $coin->sale_completed_1;
            $coin_data['sale_completed_2'] = $coin->sale_completed_2;
            $coin_data['amount_owned'] = $current_balance;
            $data['coins'][$coin->code] = $coin_data;
        }

        //get current btc balance
        $btc_balance = Bittrex::getBalance("BTC");
        $data['btc_additional_amount'] = $btc_balance['result']['Balance'];

          //send pusher event informing of latest coin trades
        broadcast(new \App\Events\PusherEvent(json_encode($data), "portfolio\\trades"));
        
        
    }




    function bittrexSell($coin, $volume, $rate) {

        //$order['success'] =  true;//TESTING

        //Place order
        $order = Bittrex::sellLimit("BTC-".$coin->code, $amount, $rate);

        if(!$order['success']) {
            //Order failed, alert me somehow

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
                'user_id' => 1
                );
            Transaction::create($transaction_info);

            return $volume;
        }
    }

    function bittrexBuy($coin, $amount, $rate) {
        
         // $order['success'] =  true;//TESTING

        //Calculate volume being bought from the amount of BTC being sold, the rate, and bittrex fees (0.25%)
        $volume = ($amount - ($amount*0.0025))/$rate; 
        
        //Place order
        $order = Bittrex::buyLimit("BTC-".$coin->code, $volume, $rate);

        if(!$order['success']) {
            //Order failed, alert me somehow

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
                'user_id' => 1
                );
            $transaction = Transaction::create($transaction_info);

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