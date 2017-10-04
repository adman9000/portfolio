<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    //
    protected $fillable = ['code', 'name', 'exchange'];




    //Relationships

    //relationships
    public function schemes() {

        return $this->belongsToMany('App\Scheme');

    }


    //Latest price record for this coin
	public function latestCoinprice()
	{
	  return $this->hasOne('App\CoinPrice')->latest();
	}

    public function coinprices24Hours()
    { 
    echo $yesterday;
      return $this->hasMany('App\CoinPrice')->where("created_at", ">=", $yesterday);
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
