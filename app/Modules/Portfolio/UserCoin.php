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
    protected $fillable = ['coin_id','user_id','exchange_coin_id','balance','available','locked'];


    protected $table = "coin_user";




    //---- TRADING ----

    public function marketSell($quantity) {

        $userExchange = $this->userExchange;
        $exchange_class = $userExchange->getExchangeClass();

        return $exchange_class->marketSell($this->exchangeCoin->code, $quantity);

    }

    public function marketBuy($quantity) {

        $userExchange = $this->userExchange;
        $exchange_class = $userExchange->getExchangeClass();

        return $exchange_class->marketBuy($this->exchangeCoin->code, $quantity);

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