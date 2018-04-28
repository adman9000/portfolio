<?php

/** Binance repo
 * Uses the Binance API, standardises data & function calls
**/


namespace App\Repositories;

use adman9000\cryptopia\CryptopiaAPI;
use adman9000\cryptopia\CryptopiaAPIFacade;
use App\Modules\Portfolio\Coin;

class CryptopiaExchange {


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
        $bapi = new CryptopiaAPI();
        $bapi->setAPI($this->api_key, $this->api_secret);

		try {
            $markets = $bapi->getTicker("BTC");
        }
        catch(\Exception $e) {
            echo "API FAILED";
            return false;
        }

        $balances = $bapi->getBalances();

        //Convert it to consistent data format

        //find the BTCUSD price
        //$btc_usd = 10000;
        $coin = Coin::with("latestCoinprice")->where("code", "BTC")->first();
        $btc_usd = $coin->latestCoinprice->usd_price;

        foreach($balances as $wallet) {

             //include BTC
            if($wallet['Symbol'] == "BTC") {

                $btc_balance +=  $wallet['Total'];

            }
            else {


                foreach($ticker as $market) {

                    if($market['Label'] == $wallet['Symbol']."/BTC") {

                    	//Calculate the BTC value of this coin and add it to the balance
                        $value = $wallet['Total'] * $market['LastPrice'];

                        $alts_btc_value += $value;

                        //Set the amount of this altcoin held, plus its BTC value if amount is >0
                        if($wallet['Total'] > 0) {
	                        $data[$wallet['Symbol']]['balance'] = $wallet['Total'];
	                        $data[$wallet['Symbol']]['btc_value'] = $value;
	                        $data[$wallet['Symbol']]['usd_value'] = $value * $btc_usd;
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
         $bapi = new CryptopiaAPI();
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

             //find the BTCUSD price
            $coin = Coin::with("latestCoinprice")->where("code", "BTC")->first();
            $btc_usd = $coin->latestCoinprice->usd_price;

            foreach($balances as $wallet) {

                 //include BTC
                if($wallet['Symbol'] == "BTC") {

                    $btc_balance +=  $wallet['Total'];
                    $return['btc']['balance'] = $wallet['Available'] + $wallet['Unconfirmed'];
                    $return['btc']['available'] = $wallet['Available'];
                    $return['btc']['locked'] = $wallet['Unconfirmed'];
                    $return['btc']['usd_value'] = $return['btc']['balance'] * $btc_usd;
                    $return['btc']['gbp_value'] = number_format($return['btc']['usd_value'] / env("USD_GBP_RATE"), 2);

                }
                else {


                    foreach($ticker as $market) {

                    if($market['Label'] == $wallet['Symbol']."/BTC") {

                            $total = $wallet['Total'];

                            //Calculate the BTC value of this coin and add it to the balance
                            $value = $total * $market['LastPrice'];

                            $alts_btc_value += $value;

                            //Set the amount of this altcoin held, plus its BTC value if amount is >0
                            if($inc_zero || $value > 0.0001) {

                                $asset = array();
                                $asset['code'] = $wallet['Symbol'];
                                $asset['balance'] = $total;
                                $asset['available'] = $wallet['Available'];
                                $asset['locked'] = $wallet['Unconfirmed'];
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


    /*** PUBLIC API CALLS **/

    /** getTicker()
    Get all the BTC markets available on this exchange with prices
    **/

    function getTicker() {

        //The ticker info to return
        $ticker = array();

        $bapi = new CryptopiaAPI();
        $bapi->setAPI($this->api_key, $this->api_secret);

        $markets = $bapi->getTicker("BTC");

         //find the BTCUSD price
        $coin = Coin::with("latestCoinprice")->where("code", "BTC")->first();

        if($coin->latestCoinprice)
            $btc_usd = $coin->latestCoinprice->usd_price;
        else $btc_usd = 0;
        
        //Loop through markets and return in standardised fashion
        foreach($markets as $market) {
            $base = "BTC";
            $arr = explode("/", $market['Label']);
            $code = $arr[0];

            if($base == "BTC") {
                
                $price_info = array("code" => $code, "btc_price"=>$market['LastPrice'], "usd_price" => $market['LastPrice'] * $btc_usd, "gbp_price" => $market['LastPrice'] * $btc_usd / env("USD_GBP_RATE"));
                  
                $ticker[] = $price_info;
   
            }
        }


        return $ticker;
    }



    //Return an array of all tradeable assets on the exchange
    function getAssets() {

        $assets = CryptopiaAPIFacade::getCurrencies();
        $return =array();
        foreach($assets as $result) {
            $row = array();
            $row['code'] = $result['Symbol'];
            $row['name'] = $result['Name'];
            $return[] = $row;
        }

        return $return;
    }

      //Return an array of all tradeable pairs on the exchange
    function getMarkets() {

        $markets = CryptopiaAPIFacade::getAssetPairs();
        $return =array();

        foreach($markets as $result) {
            //only allow BTC traded coins in here
            if($result['BaseSymbol'] == "BTC") {
                $row = array();
                $row['market_code'] = $result['Label'];
                $row['base_code'] = $result['Symbol'];
                $row['trade_code'] = $result['BaseSymbol'];
                $return[] = $row;
            }
        }

        return $return;

    }

    //get the btc usd market & gbp price as well - isnt one!
    function getBTCMarket() {

        $coin = Coin::with("latestCoinprice")->where("code", "BTC")->first();
        $price_info = array("code" => "BTC",  "usd_price" => $coin->latestCoinprice->usd_price , "gbp_price" => $coin->latestCoinprice->gbp_price);
        return $price_info;

    }


    /** Cryptopia API doesn't allow market buy & sell so use limits & pass market price in **/

    function marketSell($symbol, $quantity, $rate) {

        $api = new CryptopiaAPI();
        $api->setAPI($this->api_key, $this->api_secret);

        return $api->limitSell($symbol, $quantity, $rate);

    }

    function marketBuy($symbol, $quantity, $rate) {

        $api = new CryptopiaAPI();
        $api->setAPI($this->api_key, $this->api_secret);

        return $api->limitBuy($symbol, $quantity, $rate);

    }

    function getRecentTrades($count = 10) {

        $api = new CryptopiaAPI();
        $api->setAPI($this->api_key, $this->api_secret);

        $trades = $api->getRecentTrades(false, $count);

        $return = array();

        foreach($trades as $trade) {
            $r = array();

            $coins = explode("/", $trade['Market']);

            if($trade['Type'] == "Buy") {
                $r['coin_bought'] = $coins[0];
                $r['coin_sold'] = $coins[1];
                $r['amount_bought'] = $trade['Amount'];
                $r['amount_sold'] = $trade['Total'];
            }
            else {
                $r['coin_bought'] = $coins[1];
                $r['coin_sold'] = $coins[0];
                $r['amount_bought'] = $trade['Total'];
                $r['amount_sold'] = $trade['Amount'];
            }

            $r['exchange_rate'] = $trade['Rate'];
            $r['fees'] = $trade['Fee'];
            $r['status'] = "complete"; //This is a trade, so of course its complete!

            $return[] = $r;

        }

       return $return;

    }

    function getOrders() {

        //Cant get order history from cryptopia, only trade history!
        return $this->getRecentTrades(100);

    }

}
