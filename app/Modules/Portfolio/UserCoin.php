<?php

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;
use App\User;

class UserCoin extends Model
{
    //
    protected $fillable = ['coin_id','user_id','exchange_coin_id','balance','available','locked'];


    protected $table = "coin_user";


}
?>