<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;

class CoinPrice extends Model
{
    //
    protected $fillable = ['coin_id', 'current_price'];

    public function coin() {
    	return $this->belongsTo('App\Modules\Portfolio\Coin');
    }


    //Displaying formatted data
    public function getFormattedPrice()
	{
	    return number_format($this->attributes['current_price'], 2);
	}
}
