<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    //
    protected $guarded = [];


    //Relationships

    function user() {
    	return $this->belongsTo("\App\User");
    }

    function coin() {
    	return $this->belongsTo("\App\Modules\Portfolio\Coin")->with("latestCoinPrice");
    }

   
   /** display alert string */

   function text($split = ". ") {

   		$text = "";

   		if($this->gbp_min_value)
   			$text .= "GBP value under " . $this->gbp_min_value.$split;
   		if($this->gbp_max_value)
   			$text .= "GBP value over " . $this->gbp_max_value.$split;
   		if($this->gbp_min_price)
   			$text .= "GBP price under " . $this->gbp_min_price.$split;
   		if($this->gbp_max_price)
   			$text .= "GBP price over " . $this->gbp_max_price.$split;

   		return $text;
   }

   function currentPrice() {

   		return $this->coin->latestCoinPrice->gbp_price;

   }

   function currentValue() {

   		return $this->coin->latestCoinPrice->gbp_price * $this->balance;

   }

}
