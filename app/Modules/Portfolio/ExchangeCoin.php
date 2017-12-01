<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ExchangeCoin extends Pivot
{
    //
    protected $fillable = ['coin_id','exchange_id','code', 'market_code', 'btc_price', 'usd_price', 'gbp_price'];

    protected $table = "coin_exchange";


    /** Relationships **/

    function exchange() {

    	return $this->belongsTo("\App\Modules\Portfolio\Exchange");
    }

     function coin() {

    	return $this->belongsTo("\App\Modules\Portfolio\Coin");
    }

     //Latest price record for this coin
	public function latestCoinprice()
	{
	  return $this->hasOne('App\Modules\Portfolio\ExchangeCoinPrice', 'exchange_coin_id')->latest()->limit(2000);
	}

    public function coinprices24Hours()
    { 
      return $this->hasMany('App\Modules\Portfolio\ExchangeCoinPrice')->where("created_at", ">=", date("Y-m-d G:i:s", strtotime("24 hours ago")));
    }

    //All price records for this coin
    public function coinprices() {
        return $this->hasMany('App\Modules\Portfolio\ExchangeCoinPrice');
    }

    public function coinprice1DayAgo()
    { 
      return $this->hasOne('App\Modules\Portfolio\ExchangeCoinPrice', 'exchange_coin_id')->where("created_at", "<=", date("Y-m-d G:i:s", strtotime("24 hours ago")))->orderBy('created_at', 'DESC')->first();
    }

    public function coinprice1HourAgo()
    { 
      return $this->hasOne('App\Modules\Portfolio\ExchangeCoinPrice', 'exchange_coin_id')->where("created_at", "<=", date("Y-m-d G:i:s", strtotime("1 hour ago")))->orderBy('created_at', 'DESC')->first();
    }

    public function coinprice1WeekAgo()
    { 
      return $this->hasOne('App\Modules\Portfolio\ExchangeCoinPrice', 'exchange_coin_id')->where("created_at", "<=", date("Y-m-d G:i:s", strtotime("1 week ago")))->orderBy('created_at', 'DESC')->first();
    }

   }

   
