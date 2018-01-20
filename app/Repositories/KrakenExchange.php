<?php

/** Binance repo
 * Uses the Binance API, standardises data & function calls
**/


namespace App\Repositories;

use adman9000\kraken\KrakenAPIFacade;
use adman9000\kraken\KrakenAPI;
use App\Modules\Portfolio\ExchangeCoin;

class KrakenExchange {


        function __construct($key=false, $secret=false) {

                $this->api_key = $key;
                $this->api_secret = $secret;

        }
        
    function setAPI($key, $secret) {

         $this->api_key = $key;
        $this->api_secret = $secret;
    }


	/** getAccountStats()
	 * Returns an array of account stats for Binance
	 * btc balance, alts btc value, btc to usd exchange rate, array of altcoins held
	**/
	function getAccountStats() {

		$stats = array();

		//Actual amount of BTC held at this exchange
		$btc_balance = 0;

		//Adding up the BTC value of altcoins
		$alts_btc_value = 0;



        $stats['btc_balance'] = 0;
        $stats['alts_btc_value'] = 0;
        $stats['altcoins'] = array();
        $stats['btc_usd_rate'] = 0;

        $stats['total_btc_value'] = $stats['btc_balance'] + $stats['alts_btc_value'] ;

        //Get values of everything in USD
        $stats['btc_usd_value'] = $stats['btc_balance'] * $stats['btc_usd_rate'];
        $stats['alts_usd_value'] = $stats['alts_btc_value'] * $stats['btc_usd_rate'];
        $stats['total_usd_value'] = $stats['total_btc_value'] * $stats['btc_usd_rate'];
        
        return $stats;

	}


		/** getBalances()
	 * @param $inc_zero - include zero balances
	 * @return standardised array of balances
	 **/
	function getBalances($inc_zero=true) {

        //Actual amount of BTC held at this exchange
        $btc_balance = 0;

        //Adding up the BTC value of altcoins
        $alts_btc_value = 0;

		 //Get balances of my coins
        $kraken = new KrakenAPI();
        $kraken->setAPI($this->api_key, $this->api_secret);

        $balances = KrakenAPIFacade::getBalances();

        //Get the BTC-USD rate
       // $btc_market = KrakenAPIFacade::getTicker(array("XBTUSD"));
      //  $btc_usd = $btc_market['result']['XXBTZUSD']['l'][0];

        //Get latest prices for everythign on kraken so we can calculate current values
        //Could we just do this with the data in our db??
        //$ticker = $this->getTicker();

        //Try getting current prices from DB instead, save calling kraken again
        $exchange_coins = ExchangeCoin::where("exchange_id", 1)->where("code", "!=", "BTC")->get();

        $btc_coin = ExchangeCoin::where("exchange_id", 1)->where("code", "BTC")->get()->first();
        $btc_usd = $btc_coin->usd_price;

        //The standardised array I'm going to return
        $return = array();

        if(!$balances) return false;
        else {

        	foreach($balances['result'] as $code=>$balance) {

                    if($code == "XXBT") {

                          $btc_balance +=  $balance;
                            $return['btc']['balance'] = $balance;
                            $return['btc']['available'] = $balance;
                            $return['btc']['locked'] = 0;
                            $return['btc']['usd_value'] = $return['btc']['balance'] * $btc_usd;
                            $return['btc']['gbp_value'] = number_format($return['btc']['usd_value'] / env("USD_GBP_RATE"), 2);

                    }
                    else {

                        foreach($exchange_coins as $ecoin) {

                        if($code == $ecoin->code) {

                            $total = $balance;

                            //Calculate the BTC value of this coin and add it to the balance
                            $value = $total * $ecoin['btc_price'];

                            $alts_btc_value += $value;

                               //Set the amount of this altcoin held, plus its BTC value if amount is >0
                            if($inc_zero || $value > 0.0001) {

                                $asset = array();
                                $asset['code'] = $code;
                                $asset['balance'] = $balance;
                                $asset['available'] = $balance;
                                $asset['locked'] = 0;
                                $asset['btc_value'] = round($value, 8);
                                $asset['usd_value'] = $total * $ecoin['usd_price'];
                                $asset['gbp_value'] = $total * $ecoin['gbp_price'];
                                $return['assets'][] = $asset;
                            }
                        }

                    }
        			
        		}
        	}
        	
        }


        return $return;
	}


    //Return an array of all tradeable assets on the exchange
    function getAssets() {

        $assets = KrakenAPIFacade::getAssetInfo();
        $return =array();
        foreach($assets['result'] as $code=>$result) {
            $row = array();
            $row['alt_code'] = $result['altname'];
            $row['code'] = $code;
            $row['name'] = $result['altname'];
            $return[] = $row;
        }

        return $return;
    }

    //Return an array of all tradeable pairs on the exchange
    function getMarkets() {

        $markets = KrakenAPIFacade::getAssetPairs();

        $return =array();

        foreach($markets['result'] as $code=>$result) {
            //only allow xxbt traded coins in here
            if($result['quote'] == "XXBT") {
                $row = array();
                $row['market_code'] = $code;
                $row['base_code'] = $result['base'];
                $row['trade_code'] = $result['quote'];
                $return[] = $row;
            }
        }

        return $return;

    }


    //Return the latest prices for all BTC trades on the exchange
    function getTicker() {

        $ticker = array();
        $markets = array();

        //Get the BTC price in USD
        $btc_market = KrakenAPIFacade::getTicker(array("XBTUSD"));

        $btc_usd = $btc_market['result']['XXBTZUSD']['l'][0];

        //We need to specify with kraken which markets we want the ticker for - annoying!
        $exchange_coins = ExchangeCoin::where("exchange_id", 1)->where("code", "!=", "XBT")->get();

        foreach($exchange_coins as $mycoin) {
            if($mycoin->market_code) $markets[] = $mycoin->market_code;
        }

        //Get all the BTC markets for coins we have got recorded on kraken
        $markets = KrakenAPIFacade::getTicker($markets);

        //Loop through markets, find any of my coins and save the latest price to DB
        foreach($markets['result'] as $market_code=>$market) {
           
                
                $price_info = array("code" => $market_code, "btc_price"=>$market['l'][0], "usd_price" => $market['l'][0] * $btc_usd, "gbp_price" => $market['l'][0] * $btc_usd / env("USD_GBP_RATE"));
                  
                $ticker[] = $price_info;
   
        }


            return $ticker;
    }


     //get the btc usd market & gbp price as well
    function getBTCMarket() {

         //Get the BTC price in USD
        $market = KrakenAPIFacade::getTicker(array("XBTUSD"));

        $market = $market['result']['XXBTZUSD'];
        $price_info = array("code" => "BTC",  "usd_price" => $market['l'][0] , "gbp_price" => $market['l'][0] / env("USD_GBP_RATE"));
                return $price_info;

    }


        /** Cryptopia API doesn't allow market buy & sell so use limits & pass market price in **/

    function marketSell($symbol, $quantity, $rate) {

        $api = new KrakenAPI();
        $api->setAPI($this->api_key, $this->api_secret);

        return $api->limitSell($symbol, $quantity, $rate);

    }

    function marketBuy($symbol, $quantity, $rate) {

        $api = new KrakenAPI();
        $api->setAPI($this->api_key, $this->api_secret);

        return $api->limitBuy($symbol, $quantity, $rate);

    }

    function getRecentTrades() {

         $api = new KrakenAPI();
        $api->setAPI($this->api_key, $this->api_secret);

        $orders = $api->getRecentTrades();

        return $orders;

    }

    function getOpenOrders() {

         $api = new KrakenAPI();
        $api->setAPI($this->api_key, $this->api_secret);

        $orders = $api->getOpenOrders();

        return $orders;

    }

    function getClosedOrders() {

         $api = new KrakenAPI();
        $api->setAPI($this->api_key, $this->api_secret);

        $orders = $api->getClosedOrders();

        return $orders;

    }

    function getOrders() {

        $open = $this->getOpenOrders();
        $closed = $this->getClosedOrders();

        $open = $open['result']['open'];
        $closed = $closed['result']['closed'];
        $orders = array_merge($open, $closed);

        return $orders;
    }

}
