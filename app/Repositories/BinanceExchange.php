<?php

/** Binance repo
 * Uses the Binance API, standardises data & function calls
**/


namespace App\Repositories;

use adman9000\binance\BinanceAPI;
use adman9000\binance\BinanceAPIFacade;

class BinanceExchange {


    protected $api_key;
    protected $api_secret;

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
	 * TODO: Update this using functions from this class? getBalances etc
	**/
	function getAccountStats() {

		$stats = array();

		//Actual amount of BTC held at this exchange
		$btc_balance = 0;

		//Adding up the BTC value of altcoins
		$alts_btc_value = 0;

		//Get what we need from the API
        $bapi = new BinanceAPI();
        $bapi->setAPI($this->api_key, $this->api_secret);

		$ticker = $bapi->getTickers();
        $balances = $bapi->getBalances();

        //Convert it to consistent data format

        //find the BTCUSD ticker
        foreach($ticker as $market) {
            if($market['symbol'] == "BTCUSDT") {
                $btc_usd = $market['price'];
            }
        }

        foreach($balances as $wallet) {

             //include BTC
            if($wallet['asset'] == "BTC") {

                $btc_balance +=  $wallet['free']+ $wallet['locked'];

            }
            else {


                foreach($ticker as $market) {

                    if($market['symbol'] == $wallet['asset']."BTC") {

                    	//Calculate the BTC value of this coin and add it to the balance
                        $value = ($wallet['free'] + $wallet['locked']) * $market['price'];

                        $alts_btc_value += $value;

                        //Set the amount of this altcoin held, plus its BTC value if amount is >0
                        if($wallet['free'] > 0) {
	                        $data[$wallet['asset']]['balance'] = $wallet['free'] + $wallet['locked'];
	                        $data[$wallet['asset']]['btc_value'] = $value;
	                        $data[$wallet['asset']]['usd_value'] = $value * $btc_usd;
	                    }

                        break;

                    }
                }
            }

        }

        $stats['btc_balance'] = $btc_balance;
        $stats['alts_btc_value'] = $alts_btc_value;
        $stats['altcoins'] = $data;
        $stats['btc_usd_rate'] = $btc_usd;

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
         $bapi = new BinanceAPI();
        $bapi->setAPI($this->api_key, $this->api_secret);

         try {
            $ticker = $bapi->getTickers();
            $balances = $bapi->getBalances();
        }
        catch(\Exception $e) {
            echo "API FAILED";
            return false;
        }

        //The standardised array I'm going to return
        $return = array();


        if(!$balances) return false;
        else {

             //find the BTCUSD ticker
            foreach($ticker as $market) {
                if($market['symbol'] == "BTCUSDT") {
                    $btc_usd = $market['price'];
                }
            }

            foreach($balances as $wallet) {

                 //include BTC
                if($wallet['asset'] == "BTC") {

                    $btc_balance +=  $wallet['free'];
                    $return['btc']['balance'] = $wallet['free'] + $wallet['locked'];
                    $return['btc']['available'] = $wallet['free'];
                    $return['btc']['locked'] = $wallet['locked'];
                    $return['btc']['usd_value'] = $return['btc']['balance'] * $btc_usd;
                    $return['btc']['gbp_value'] = number_format($return['btc']['usd_value'] / env("USD_GBP_RATE"), 2);

                }
                else {


                    foreach($ticker as $market) {

                        if($market['symbol'] == $wallet['asset']."BTC") {

                            $total = $wallet['free'] + $wallet['locked'];

                            //Calculate the BTC value of this coin and add it to the balance
                            $value = $total * $market['price'];

                            $alts_btc_value += $value;

                            //Set the amount of this altcoin held, plus its BTC value if amount is >0
                            if($inc_zero || $value > 0.0001) {

                                $asset = array();
                                $asset['code'] = $wallet['asset'];
                                $asset['balance'] = $total;
                                $asset['available'] = $wallet['free'];
                                $asset['locked'] = $wallet['locked'];
                                $asset['btc_value'] = round($value, 8);
                                $asset['usd_value'] = $value * $btc_usd;
                                $asset['gbp_value'] = number_format($asset['usd_value'] / env("USD_GBP_RATE"), 2);
                                $return['assets'][] = $asset;

                            }

                            break;

                        }
                    }
                }
            	
            }
        }

        return $return;
	}


    /** getTicker()
    Get all the BTC markets available on this exchange with prices
    **/

    function getTicker() {

        //The ticker info to return
        $ticker = array();

        $bapi = new BinanceAPI();
        $bapi->setAPI($this->api_key, $this->api_secret);

        try {
            $markets = $bapi->getTickers();
        }
        catch(\Exception $e) {
            echo "API FAILED";
            return false;
        }

         //find the BTCUSD ticker
        foreach($markets as $market) {
            if($market['symbol'] == "BTCUSDT") {
                $btc_usd = $market['price'];
            }
        }

        //Loop through markets, find any of my coins and save the latest price to DB
        foreach($markets as $market) {
            $base = substr($market['symbol'], strlen($market['symbol'])-3, 3);
            $code = substr($market['symbol'], 0, strlen($market['symbol'])-3);

            if($base == "BTC") {
                
                $price_info = array("code" => $code, "btc_price"=>$market['price'], "usd_price" => $market['price'] * $btc_usd, "gbp_price" => $market['price'] * $btc_usd / env("USD_GBP_RATE"));
                  
                $ticker[] = $price_info;
   
            }
        }


        return $ticker;
    }



    //Return an array of all tradeable assets on the exchange
    //Got to use the market pairs data as no currency list for binance api
    function getAssets() {

        $assets = BinanceAPIFacade::getMarkets();
        $return =array();
      
        foreach($assets as $result) {
            if($result['quoteAsset'] == "BTC") {
                $row = array();
                $row['code'] = $result['baseAsset'];
                $row['name'] = $result['baseAsset'];
                $return[] = $row;
            }
        }

        return $return;
    }

      //Return an array of all tradeable pairs on the exchange
    function getMarkets() {

        $markets = BinanceAPIFacade::getMarkets();
        $return =array();

        foreach($markets as $result) {
            //only allow BTC traded coins in here
            if($result['quoteAsset'] == "BTC") {
                $row = array();
                $row['market_code'] = $result['symbol'];
                $row['base_code'] = $result['baseAsset'];
                $row['trade_code'] = $result['quoteAsset'];
                $return[] = $row;
            }
        }

        return $return;

    }


    //get the btc usd market & gbp price as well
    function getBTCMarket() {

        $bapi = new BinanceAPI();
        $bapi->setAPI($this->api_key, $this->api_secret);

        try {
            $markets = $bapi->getTicker("BTCUSDT");
        }
        catch(\Exception $e) {
            echo "API FAILED";
            return false;
        }
         //find the BTCUSD ticker
        foreach($markets as $market) {
            if($market['symbol'] == "BTCUSDT") {
                 $price_info = array("code" => "BTC",  "usd_price" => $market['price'] , "gbp_price" => $market['price'] / env("USD_GBP_RATE"));
                return $price_info;
            }
        }

    }



    function marketSell($symbol, $quantity, $rate=false) {

        $bapi = new BinanceAPI();
        $bapi->setAPI($this->api_key, $this->api_secret);


        //Make sure quantity is within step limits

        $markets = $bapi->getMarkets();

		$step = 1;
        foreach($markets as $market) {
            if(($market['baseAsset'] == $symbol) && ($market['quoteAsset'] == "BTC")) {
                $step = $market['filters'][1]['stepSize'];
            }
        }
		
        $quantity = floor($quantity/$step) * $step;

        return $bapi->marketSell($symbol, $quantity);

    }

    function marketBuy($symbol, $quantity, $rate=false) {

        $bapi = new BinanceAPI();
        $bapi->setAPI($this->api_key, $this->api_secret);


        //Make sure quantity is within step limits

        $markets = $bapi->getMarkets();

		$step = 1;
        foreach($markets as $market) {
            if(($market['baseAsset'] == $symbol) && ($market['quoteAsset'] == "BTC")) {
                $step = $market['filters'][1]['stepSize'];
            }
        }

        $quantity = floor($quantity/$step) * $step;

        return $bapi->marketBuy($symbol, $quantity);

    }



  // Pointless. Binance doesnt let you get trade or order history unless you specify the market, so this is not going to work!

  function getRecentTrades($market, $count = 10) {

        $api = new BinanceAPI();
        $api->setAPI($this->api_key, $this->api_secret);

        $trades = $api->getRecentTrades($market, $count);

        return $trades;

    }

    function getOrders($market) {

         $api = new BinanceAPI();
        $api->setAPI($this->api_key, $this->api_secret);

        $orders = $api->getAllOrders($market);

        return $orders;

    }


}
