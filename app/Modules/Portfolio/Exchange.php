<?php

namespace App\Modules\Portfolio;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\BittrexExchange;
use App\Repositories\BinanceExchange;
use App\Repositories\KrakenExchange;
use App\Repositories\CryptopiaExchange;

use adman9000\cryptoexchange\CryptoExchange;

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

            case "cryptopia" :
                return new CryptopiaExchange($this->api_key, $this->api_secret);
                break;

        }
        return false;
    }


    /* retreivePrices()
     * Get the latest prices from this exchange and save to DB
    */
    function retrievePrices($time) {


        $class = $this->getExchangeClass();

        $ticker =  $class->getTicker();

        $btc_market =  $class->getBTCMarket();

        if(!$ticker) return false;
        if(!$btc_market) return false;

        //Loop through markets, find any of my coins and save the latest price to DB
        foreach($ticker as $market) {
            foreach($this->coins as $coin) {
                if(($coin->code == $market['code']) || ($coin->market_code == $market['code'])) {
                    $price_info = array("created_at" => $time, "coin_id"=>$coin->coin_id, "exchange_id"=>$this->id, "exchange_coin_id"=>$coin->id, "btc_price"=>$market['btc_price'], "usd_price"=>$market['usd_price'], "gbp_price"=>$market['gbp_price']);

                    $price = ExchangeCoinPrice::create($price_info);

                }
            }
        }

        //DO BTC SEPARATE
        if($btc_market) {
            foreach($this->coins as $coin) {
                if($coin->code == "BTC") {
                    $price_info = array("created_at" => $time, "coin_id"=>$coin->coin_id, "exchange_id"=>$this->id, "exchange_coin_id"=>$coin->id, "btc_price"=>1, "usd_price"=>$btc_market['usd_price'], "gbp_price"=>$btc_market['gbp_price']);

                    $price = ExchangeCoinPrice::create($price_info);
                }
            }
        }

    }


    //A one-off function to set up all the markets for an exchange. Needs manual checking as some codes will be different
    //Basically we want to know all the coins that can be traded with BTC on this exchange
    function setupCoins() {

        //Cheat with BTC
        $coin = Coin::where('code', 'BTC')->get()->first();
        $coin_info = array("coin_id"=>$coin->id, "exchange_id"=>$this->id, "code"=>"BTC");
        ExchangeCoin::firstOrCreate($coin_info);


    	$coins = Coin::all();

        $exchange = new CryptoExchange($this->slug, $this->api_key, $this->api_secret);
        //$assets = $exchange->getCurrencies();
        $ticker = $exchange->getTickers();
        //$markets = $exchange->getMarkets();

        dd($ticker);

/**
        $class = $this->getExchangeClass();


        //$ticker = $class->getTicker();

        //dd($ticker);

        $assets =  $class->getAssets();

        $markets =  $class->getMarkets();
**/

        //dd($markets);

        foreach($assets as $asset) {

            $accepted = false;

            //Make sure there is a BTC market for this asset on this exchange
            foreach($markets as $market) {

                if($asset['code'] == $market['base_code']) {
                    $accepted = true;
                    $market_code = $market['market_code'];
                    break;
                }

            }

            if($accepted) {

                //Find the coin in our database and link it to this exchange
                foreach($coins as $coin) {
                
                    if(($coin->code == $asset['code']) || (isset($asset['alt_code']) && ($coin->code == $asset['alt_code']))) {

                    	$coin_info = array("coin_id"=>$coin->id, "exchange_id"=>$this->id, "code"=>$asset['code']);
                        $values = array('market_code'=>$market_code);
                        $instance = ExchangeCoin::where($coin_info);
                        if ($instance->count() != 0) {
                            DB::table('coin_exchange')->where($coin_info)->update($values);
                            
                        } else {
                            $instance = ExchangeCoin::updateOrCreate($coin_info, $values);
                        }
                    }
                }

            }

        }

    }



    /** Relationships **/

    function coins() {

    	return $this->hasMany('\App\Modules\Portfolio\ExchangeCoin');
    }

}


   
