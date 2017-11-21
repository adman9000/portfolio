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

		$ticker = $bapi->getTicker();
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

                $btc_balance +=  $wallet['free'];

            }
            else {


                foreach($ticker as $market) {

                    if($market['symbol'] == $wallet['asset']."BTC") {

                    	//Calculate the BTC value of this coin and add it to the balance
                        $value = $wallet['free'] * $market['price'];

                        $alts_btc_value += $value;

                        //Set the amount of this altcoin held, plus its BTC value if amount is >0
                        if($wallet['free'] > 0) {
	                        $data[$wallet['asset']]['btc_value']['balance'] = $wallet['free'] + $wallet['locked'];
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

        $ticker = $bapi->getTicker();
        $balances = $bapi->getBalances();

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

}
