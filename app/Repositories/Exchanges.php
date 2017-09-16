<?php
/** Maybe have a separate repository file for kraken and bittrex **/

namespace App\Repositories;

use App\Coin;
use App\CoinPrice;
use adman9000\kraken\KrakenAPIFacade;
use adman9000\Bittrex\Bittrex;

class Exchanges {

	function __construct() {
        
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
        broadcast(new \App\Events\PusherEvent(json_encode($data)));
    }


    /* Should run after we have got latest prices
    */
    function runTradingRules() {

        $coins = Coin::with('latestCoinprice')->get();

        $balances = Bittrex::getBalances();

        $my_balances = array();
        foreach($balances['result'] as $balance) {
            $my_balances[$balance['Currency']] = $balance['Balance'];
        }

        foreach($coins as $coin) {

            //Get current price
            $current_price = $coin->latestCoinPrice->current_price;

            //Get current balance
            isset($my_balances[$coin->code] ? $current_balance = $my_balances[$coin->code] : $current_balance = 0;

            //Always update highest price point if current price is higher
            $coin->highest_price = max($coin->highest_price, $current_price);

         //Those with a current price at least that of $sale_point_1 sell 50% for BTC

            //Calculate the first sell point
            $sell_point_1 = $coin->buy_point * 2;
            
            if(($current_price >= $sell_point_1) && (!$coin->sale_completed_1)) {
               
                //Sell first 50%
             //    $order_ok = $this->bittrexSell($coin, $current_balance, $current_price);

                //Set sale_completed=1
                 if($order_ok) $coin->sale_completed_1 = true;
            }


            //Those with a current price at least that of $sale_point_2 set the $sale_trigger_2 variable and record current price in $price_high if higher than current $price_high
            
            //Calculate second sell point
            $sell_point_2 = $coin->buy_point * 5;
            
            if(($current_price >= $sell_point_2) && (!$coin->sale_completed_2)) {

                if(!$coin->sale_trigger_2) {
                    //Set trigger to sell when price drops 5%
                    $coin->sale_trigger_2 = 1;
                }
                else {
                     //Those with $sale_trigger_2 set, check if current price is 5% lower than $price_high. If so sell remaining stock and double the buy in price.
                    //Check for 5% price drop from highest point
                    if($current_price <= $coin->highest_price - ($coin->highest_price/20)) {

                        //Sell remainder
                       // $this->bittrexSell($coin, $current_balance, $current_price);

                        //Reset triggers, double buy in price
                        $coin->sale_trigger_2 = 0;
                        $coin->sale_completed_1 = 0;
                        $coin->buy_point = $coin->buy_point * 2;
                    }
                }
            }

            //Coins that have dropped below the buy point and have not yet been bought, can be bought

            if( ($current_price <= $coin->buy_point) && (!$coin->been_bought) ) {

              //  $this->bittrexBuy($coin, 0.06);

                //St the bought flag
                $coin->been_bought = 1;
            }

            //Coins that have dropped below the buy point and have had 50% sold can be bought again (50%)
             if( ($current_price <= $coin->buy_point) && ($coin->been_bought) && ($coin->sale_completed_1) ) {

               // $this->bittrexBuy($coin, 0.06);

                 //unset the sale_completed_1 flag
                $coin->sale_completed_1 = 0;
            }

            //Save updated coin details
            $coin->save();

        }
    }




    function bittrexSell($coin, $amount, $rate) {
        return true;//TESTING
        /* $order = Bittrex::sellLimit("BTC-".$coin->code, $amount, $rate);

        if(!$order['success']) {
            //Order failed, alert me somehow

            return false;
        }
        else {
             //Order successful, save transaction to DB
            $transaction_info = array(
                "coin_sold" => $coin->id;
                "amount_sold" => $volume;
                "exchange_rate" => $rate;
                );
            Transaction::create($transaction_info);

            return true;
        }*/
    }

    function bittrexBuy($coin, $amount, $rate) {
        return true; //TESTING
        /*$volume = ($amount - 0.0001)/$rate;
         $order = Bittrex::buyLimit("BTC-".$coin->code, $volume, $rate);

        if(!$order['success']) {
            //Order failed, alert me somehow

            return false;
        }
        else {
             //Order successful, save transaction to DB
            $transaction_info = array(
                "coin_bought" => $coin->id;
                "amount_sold" => $volume;
                "exchange_rate" => $rate;
                );
            Transaction::create($transaction_info);

            return true;
        }*/
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