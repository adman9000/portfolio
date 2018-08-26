<?php
/** userCoin
 * managing coins held in exchanges
 **/

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;
use App\User;

class UserCoin extends Model
{
    //
    protected $fillable = ['coin_id','user_id','exchange_coin_id','balance','available','locked', 'user_exchange_id'];


    protected $table = "coin_user";




    //---- TRADING ----

    public function marketSell($quantity) {

        $userExchange = $this->userExchange;
        $exchange_class = $userExchange->getExchangeClass();

        //Some exchanges don't do market trades through API so we have to send current price
        $this->load("exchangeCoin");
        //Knock off 1% to make sure we are below current market price so it will sell (usually)
        $rate = $this->exchangeCoin->btc_price*0.99;

        $exchange_class->marketSell($this->exchangeCoin->market_code, $quantity, $rate);

    }

    public function marketBuy($quantity) {

        $userExchange = $this->userExchange;
        $exchange_class = $userExchange->getExchangeClass();

        //Some exchanges don't do market trades through API so we have to send current price
        $this->load("exchangeCoin");
        //Add on 1% to make sure we are above current market price so it will buy (usually)
        $rate = $this->exchangeCoin->btc_price*1.01;

    }

    function withdraw($amount, $address) {

        $userExchange = $this->userExchange;
        $exchange_class = $userExchange->getExchangeClass();
        $exchange_class->withdraw($this->exchangeCoin->code, $amount, $address);

    }

    // Relationships
     function coin() {

    	return $this->belongsTo("\App\Modules\Portfolio\Coin");
    }

    function user() {

    	return $this->belongsTo("\App\User");
    }


    function userExchange() {

        return $this->belongsTo("\App\Modules\Portfolio\UserExchange");
    }

  function exchangeCoin() {

    	return $this->belongsTo("\App\Modules\Portfolio\ExchangeCoin");
    }
}
?>