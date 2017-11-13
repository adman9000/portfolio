<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CoinScheme extends Pivot
{
    //
    protected $guarded = [];

    public function coin() {
    	return $this->belongsTo('App\Coin');
    }

    public function scheme() {
    	return $this->belongsTo('App\Scheme');
    }
}
