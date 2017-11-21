<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CoinScheme extends Pivot
{
    //
    protected $guarded = [];

    public function coin() {
    	return $this->belongsTo('App\Modules\Portfolio\Coin');
    }

    public function scheme() {
    	return $this->belongsTo('App\Modules\Portfolio\Scheme');
    }
}
