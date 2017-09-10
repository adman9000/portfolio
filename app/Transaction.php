<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class  Transaction extends Model
{
    //
    protected $guarded = [];


    //relationships
    public function user() {
    	return $this->belongsTo('App\User');
    }


    public function coinSold(){
    	return $this->belongsTo('App\Coin', 'coin_sold_id');
    }
      public function coinBought(){
    	return $this->belongsTo('App\Coin', 'coin_bought_id');
    }
}