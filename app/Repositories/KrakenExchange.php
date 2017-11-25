<?php

/** Binance repo
 * Uses the Binance API, standardises data & function calls
**/


namespace App\Repositories;

use adman9000\kraken\KrakenAPIFacade;

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

		 //Get balances of my coins
        $balances = KrakenAPIFacade::getBalances();

        //The standardised array I'm going to return
        $return = array();


        if(!$balances) return false;
        else {

        	foreach($balances['result'] as $code=>$balance) {
        		if(($inc_zero) || ($balance>0)) {
        			$asset['code'] = $code;
        			$asset['balance'] = $balance;
        			$return[] = $asset;
        		}
        	}
        	
        }

        return $return;
	}


        function getTicker() {

                return array();
        }

}
