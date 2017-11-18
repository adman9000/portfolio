<?php

/** Binance repo
 * Uses the Binance API, standardises data & function calls
**/


namespace App\Repositories;

use adman9000\binance\BinanceAPIFacade;

class BinanceExchange {


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
		$ticker = BinanceAPIFacade::getTicker();
        $balances = BinanceAPIFacade::getBalances();

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
	                        $data[$wallet['asset']]['btc_value']['balance'] = $wallet['free'] ;
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

		 //Get balances of my coins
        $balances = BinanceAPIFacade::getBalances();

        //The standardised array I'm going to return
        $return = array();


        if(!$balances) return false;
        else {

        	foreach($balances as $balance) {
        		$total = $balance['free'] + $balance['locked'];
        		if(($inc_zero) || ($total>0)) {
        			$asset['code'] = $balance['asset'];
        			$asset['balance'] = $total;
        			$asset['available'] = $balance['free'];
        			$return[] = $asset;
        		}
        	}
        	
        }

        return $return;
	}

}
