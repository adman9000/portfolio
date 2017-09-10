<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    //
    protected $fillable = ['code', 'name'];




    //Relationships

    //Latest price record for this coin
	public function latestCoinprice()
	{
	  return $this->hasOne('App\CoinPrice')->latest();
	}

    //All price records for this coin
    public function coinprices() {
        return $this->hasMany('App\CoinPrice');
    }

    public function transactionsSold() {
        return $this->hasMany('App\Transaction', 'coin_sold_id');
    }

    public function transactionsBought() {
        return $this->hasMany('App\Transaction', 'coin_bought_id');
    }
}
