<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CoinPrice extends Model
{
    //
    protected $fillable = ['coin_id', 'current_price'];

     public function coin() {
    	return $this->belongsTo('App\Coin');
    }


    //Displaying formatted data
    public function getFormattedPrice()
	{
	    return number_format($this->attributes['current_price'], 2);
	}
}
