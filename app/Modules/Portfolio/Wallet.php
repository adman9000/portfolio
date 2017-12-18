<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
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

    function values() {
    	return $this->hasMany("\App\Modules\Portfolio\WalletValue");
    }

    public function value1DayAgo()
    { 
      return $this->hasOne('App\Modules\Portfolio\WalletValue', 'wallet_id')->where("created_at", "<=", date("Y-m-d G:i:s", strtotime("24 hours ago")))->orderBy('created_at', 'DESC')->first();
    }

    public function value1HourAgo()
    { 
      return $this->hasOne('App\Modules\Portfolio\WalletValue', 'wallet_id')->where("created_at", "<=", date("Y-m-d G:i:s", strtotime("1 hour ago")))->orderBy('created_at', 'DESC')->first();
    }

    public function value1WeekAgo()
    { 
      return $this->hasOne('App\Modules\Portfolio\WalletValue', 'wallet_id')->where("created_at", "<=", date("Y-m-d G:i:s", strtotime("1 week ago")))->orderBy('created_at', 'DESC')->first();
    }
   
}
