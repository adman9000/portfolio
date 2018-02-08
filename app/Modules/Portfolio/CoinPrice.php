<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;

class CoinPrice extends Model
{
    //
    protected $fillable = ['coin_id', 'btc_price', 'usd_price', 'gbp_price', 'current_supply', 'price_at', 'created_at'];

    protected $table = "cmc_prices";

    public function coin() {
    	return $this->belongsTo('App\Modules\Portfolio\Coin');
    }


    //Displaying formatted data
    public function getFormattedPrice()
	{
	    return number_format($this->attributes['current_price'], 2);
	}
}
