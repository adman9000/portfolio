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

        $btc_market =  $class->getBTCMarket();

        //Loop through markets, find any of my coins and save the latest price to DB
        foreach($ticker as $market) {
            foreach($this->coins as $coin) {
                if($coin->code == $market['code']) {
                    $price_info = array("coin_id"=>$coin->coin_id, "exchange_id"=>$this->id, "exchange_coin_id"=>$coin->id, "btc_price"=>$market['btc_price'], "usd_price"=>$market['usd_price'], "gbp_price"=>$market['gbp_price']);

                    $price = ExchangeCoinPrice::create($price_info);
                }
            }
        }

        //DO BTC SEPARATE
        if($btc_market) {
            foreach($this->coins as $coin) {
                if($coin->code == "BTC") {
                    $price_info = array("coin_id"=>$coin->coin_id, "exchange_id"=>$this->id, "exchange_coin_id"=>$coin->id, "btc_price"=>1, "usd_price"=>$btc_market['usd_price'], "gbp_price"=>$btc_market['gbp_price']);

                    $price = ExchangeCoinPrice::create($price_info);
                }
            }
        }

        
    }


    //A one-off function to set up all the markets for an exchange. Needs manual checking as some codes will be different
    function setupCoins() {

        //Cheat with BTC
        $coin = Coin::where('code', 'BTC')->get()->first();
        $coin_info = array("coin_id"=>$coin->id, "exchange_id"=>$this->id, "code"=>"BTC");
        ExchangeCoin::firstOrCreate($coin_info);


    	$coins = Coin::all();

        $class = $this->getExchangeClass();

        $ticker =  $class->getTicker();

        foreach($ticker as $market) {
            foreach($coins as $coin) {
                if($coin->code == $market['code']) {
                	$coin_info = array("coin_id"=>$coin->id, "exchange_id"=>$this->id, "code"=>$market['code']);
                	ExchangeCoin::firstOrCreate($coin_info);
                }
            }
        }

    }



    /** Relationships **/

    function coins() {

    	return $this->hasMany('\App\Modules\Portfolio\ExchangeCoin');
    }

}


   
