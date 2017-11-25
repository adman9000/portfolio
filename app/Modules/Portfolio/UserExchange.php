<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\BittrexExchange;
use App\Repositories\BinanceExchange;
use App\Repositories\KrakenExchange;
use App\User;

class UserExchange extends Model
{
    //
    protected $fillable = [];


    protected $table = "users_exchanges";



 	//relationships
    public function user() {
    	return $this->belongsTo('App\Modules\Portfolio\User');
    }

    public function exchange() {
        return $this->belongsTo('App\Modules\Portfolio\Exchange');
    }



    function getExchangeClass() {


         switch($this->exchange->slug) {

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

    /* getAccountStats()
     * @param exchange name
     * @return array of stats for users account on given exchange (or all if no params passed)
    **/
    public function getAccountStats() {


         $class = $this->getExchangeClass();
        if($class) return $class->getAccountStats();
        else return false;
         

    }


    /** getBalances()
     * return coin balances for the given exchange in consistent format
    **/
    public function getBalances($inc_zero=true) {

        //Must be an exchange selected
        if(!$class = $this->getExchangeClass()) return false;
        else {
            return $class->getBalances($inc_zero);
        }

    }

   }

   