<?php
/** Maybe have a separate repository file for kraken and bittrex **/

namespace App\Repositories;

use App\Coin;
use App\CoinPrice;
use adman9000\kraken\KrakenAPIFacade;
use adman9000\Bittrex\Bittrex;
use App\Repositories\Exchanges;

class Exchanges {

	
	 /** getPrices
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