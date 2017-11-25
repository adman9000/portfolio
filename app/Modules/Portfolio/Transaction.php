<?php

namespace App\Modules\Portfolio;

use App\User;

use Illuminate\Database\Eloquent\Model;

class  Transaction extends Model
{
    //
    protected $guarded = [];


    //relationships
    public function user() {
    	return $this->belongsTo('App\User');
    }

    public function scheme() {
        return $this->belongsTo('App\Modules\Portfolio\Scheme');
    }

    public function coinSold(){
    	return $this->belongsTo('App\Modules\Portfolio\Coin', 'coin_sold_id');
    }
    
    public function coinBought(){
    	return $this->belongsTo('App\Modules\Portfolio\Coin', 'coin_bought_id');
    }

    //TODO: coinscheme relationships?
}
