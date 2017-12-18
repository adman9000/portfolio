<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;

class WalletValue extends Model
{
    //
    protected $guarded = [];


    //Relationships

    function user() {
    	return $this->belongsTo("\App\User");
    }

    function coin() {
    	return $this->belongsTo("\App\Modules\Portfolio\Coin");
    }

    function wallet() {
    	return $this->belongsTo("\App\Modules\Portfolio\Wallet");
    }
   
}
