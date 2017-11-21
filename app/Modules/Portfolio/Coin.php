<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    //
    protected $fillable = ['code', 'name', 'exchange'];




    //Relationships

    //relationships
    public function schemes() {

        return $this->belongsToMany('App\Modules\Portfolio\Scheme')->using('App\CoinScheme')->withPivot('id','set_price', 'been_bought', 'amount_held', 'sale_1_completed', 'sale_2_completed', 'sale_1_triggered', 'sale_2_triggered', 'highest_price');;
    }


    //Latest price record for this coin
	public function latestCoinprice()
	{
	  return $this->hasOne('App\Modules\Portfolio\CoinPrice')->latest()->limit(200);
	}

    public function coinprices24Hours()
    { 
      return $this->hasMany('App\Modules\Portfolio\CoinPrice')->where("created_at", ">=", $yesterday);
    }

    //All price records for this coin
    public function coinprices() {
        return $this->hasMany('App\Modules\Portfolio\CoinPrice');
    }

    public function transactionsSold() {
        return $this->hasMany('App\Modules\Portfolio\Transaction', 'coin_sold_id');
    }

    public function transactionsBought() {
        return $this->hasMany('App\Modules\Portfolio\Transaction', 'coin_bought_id');
    }
}
