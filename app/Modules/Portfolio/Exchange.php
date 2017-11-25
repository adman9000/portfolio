<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\BittrexExchange;
use App\Repositories\BinanceExchange;
use App\Repositories\KrakenExchange;

class Exchange extends Model
{
    //
    protected $fillable = ['slug', 'title'];

    protected $api_key, $api_secret;


   function getExchangeClass() {


         switch($this->slug) {

              case "bittrex" :
                return new BittrexExchange($this->api_key, $this->api_secret);
                break;

            case "binance" :
                return new BinanceExchange($this->api_key, $this->api_secret);
                break;
                
            case "kraken" :
                return new KrakenExchange($this->api_key, $this->api_secret);
                break;

        }
        return false;
    }


    /* retreivePrices()
     * Get the latest prices from this exchange and save to DB
    */
    function retrievePrices() {


        $class = $this->getExchangeClass();

        $ticker =  $class->getTicker();

        //Loop through markets, find any of my coins and save the latest price to DB
        foreach($ticker as $market) {
            foreach($this->coins as $coin) {
                if($coin->code == $market['code']) {
                    $price_info = array("coin_id"=>$coin->coin_id, "exchange_id"=>$this->id, "exchange_coin_id"=>$coin->id, "btc_price"=>$market['price']);

                    $price = ExchangeCoinPrice::create($price_info);
                }
            }
        }
    }


    //A one-off function to set up all the markets for an exchange. Needs manual checking as some codes will be different
    function setupCoins() {

    	$coins = Coin::all();

        $class = $this->getExchangeClass();

        $ticker =  $class->getTicker();

        foreach($ticker as $market) {
            foreach($coins as $coin) {
                if($coin->code == $market['code']) {
                	$coin_info = array("coin_id"=>$coin->id, "exchange_id"=>$this->id, "code"=>$market['code']);
                	ExchangeCoin::create($coin_info);
                }
            }
        }

    }



    /** Relationships **/

    function coins() {

    	return $this->hasMany('\App\Modules\Portfolio\ExchangeCoin');
    }

}


   
