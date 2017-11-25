<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;

class ExchangeCoin extends Model
{
    //
    protected $fillable = ['coin_id','exchange_id','code'];

    protected $table = "exchanges_coins";


    /** Relationships **/

    function exchange() {

    	return $this->belongsTo("\App\Modules\Portfolio\Exchange");
    }
   }

   
