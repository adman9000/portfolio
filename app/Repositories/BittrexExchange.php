<?php

/** Binance repo
 * Uses the Binance API, standardises data & function calls
**/


namespace App\Repositories;

use adman9000\Bittrex\Bittrex;

class BittrexExchange {


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

		 //Get balances of my coins
        $balances = Bittrex::getBalances();

        //Get the BTC-USD rate
        $btc_market = Bittrex::getMarketSummary("USDT-BTC");
        $btc_usd = $btc_market['result'][0]['Last'];

        //Get latest markets for everythign on bittrex
        $markets = Bittrex::getMarketSummaries();


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

		 //Get balances of my coins
        $balances = Bittrex::getBalances();

        //The standardised array I'm going to return
        $return = array();

        if(!$balances['result']) return false;
        else {

        	foreach($balances['result'] as $balance) {
        		if(($inc_zero) || ($balance['Balance']>0)) {
        			$asset['code'] = $balance['Currency'];
        			$asset['balance'] = $balance['Balance'];
        			$asset['available'] = $balance['available'];
        			$return[] = $asset;
        		}
        	}
        	
        }

        return $return;
	}

}
