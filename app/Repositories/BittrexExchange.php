<?php

/** Binance repo
 * Uses the Binance API, standardises data & function calls
**/


namespace App\Repositories;

use adman9000\Bittrex\Bittrex;
use adman9000\Bittrex\Client;

class BittrexExchange {


    function __construct($key=false, $secret=false) {

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


        //Get what we need from the API
        $bapi = new Client(config("bittrex.auth"), config("bittrex.urls"));
        $bapi->setAPI($this->api_key, $this->api_secret);

		 //Get balances of my coins
        $balances = $bapi->getBalances();

        //Get the BTC-USD rate
        $btc_market = $bapi->getMarketSummary("USDT-BTC");
        $btc_usd = $btc_market['result'][0]['Last'];

        //Get latest markets for everythign on bittrex
        $markets = $bapi->getMarketSummaries();


        foreach($balances['result'] as $balance) {

            //include BTC
            if($balance['Currency'] == "BTC") {

                $btc_balance = $balance['Balance'];

            }
            else {


                foreach($markets['result'] as $market) {

                    if($market['MarketName'] == 'BTC-'.$balance['Currency']) {


                        $value = $balance['Balance'] * $market['Last'];

                        $alts_btc_value += $value;

                          //Set the amount of this altcoin held, plus its BTC value if amount is >0
                        if($balance['Balance'] > 0) {
	                        $data[$balance['Currency']]['btc_value']['balance'] = $balance['Balance'] ;
	                        $data[$balance['Currency']]['btc_value'] = $value;
	                        $data[$balance['Currency']]['usd_value'] = $value * $btc_usd;
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


        //Get what we need from the API
        $bapi = new Client(config("bittrex.auth"), config("bittrex.urls"));
        $bapi->setAPI($this->api_key, $this->api_secret);

         //Get balances of my coins
        $balances = $bapi->getBalances();

        //Get the BTC-USD rate
        $btc_market = $bapi->getMarketSummary("USDT-BTC");
        $btc_usd = $btc_market['result'][0]['Last'];

        //Get latest markets for everythign on bittrex
        $ticker = $bapi->getMarketSummaries();

        //The standardised array I'm going to return
        $return = array();

        if(!$balances['result']) return false;
        else {

            foreach($balances['result'] as $wallet) {

                 //include BTC
                if($wallet['Currency'] == "BTC") {

                    $btc_balance +=  $wallet['Balance'];
                    $return['btc']['balance'] = $wallet['Balance'];
                    $return['btc']['available'] = $wallet['Available'];
                    $return['btc']['locked'] = $wallet['Pending'];
                    $return['btc']['usd_value'] = $return['btc']['balance'] * $btc_usd;
                    $return['btc']['gbp_value'] = number_format($return['btc']['usd_value'] / env("USD_GBP_RATE"), 2);

                }
                else {


                    foreach($ticker['result'] as $market) {

                        if($market['MarketName'] == "BTC-".$wallet['Currency']) {

                            $total = $wallet['Balance'];

                            //Calculate the BTC value of this coin and add it to the balance
                            $value = $total * $market['Last'];

                            $alts_btc_value += $value;

                            //Set the amount of this altcoin held, plus its BTC value if amount is >0
                            if($inc_zero || $value > 0.0001) {

                                $asset = array();
                                $asset['code'] = $wallet['Currency'];
                                $asset['balance'] = $total;
                                $asset['available'] = $wallet['Available'];
                                $asset['locked'] = $wallet['Pending'];
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