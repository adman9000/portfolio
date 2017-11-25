<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;
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

        $exchange = $this->exchange->getExchangeClass();
        $exchange->setAPI($this->api_key, $this->api_secret);
        return $exchange;
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

   