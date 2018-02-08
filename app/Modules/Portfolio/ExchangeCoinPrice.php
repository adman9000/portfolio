<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;

class ExchangeCoinPrice extends Model
{
    //
   
    protected $fillable = ['coin_id', 'exchange_id','exchange_coin_id','btc_price','usd_price','gbp_price', 'price_at'];

    protected $table = "exchanges_prices";

    public function coin() {
    	return $this->belongsTo('App\Modules\Portfolio\Coin');
    }


    //Displaying formatted data
    public function getFormattedPrice()
	{
	    return number_format($this->attributes['current_price'], 2);
	}
}
