<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class scheme extends Model
{
    //
    protected $guarded = [];

    //relationships
    public function coins() {

        return $this->belongsToMany('App\Coin')->withPivot('set_price', 'been_bought', 'amount_held', 'sale_1_completed', 'sale_2_completed', 'sale_1_triggered', 'sale_2_triggered', 'highest_price');;

    }

     public function transactions() {
        return $this->hasMany('App\Transaction');
    }

}
