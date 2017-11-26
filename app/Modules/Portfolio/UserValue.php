<?php
/* Stores the value of the users portfolio */

namespace App\Modules\Portfolio;

use Illuminate\Database\Eloquent\Model;

class UserValue extends Model
{
    //
    protected $fillable = ['btc_value','usd_value','gbp_value','user_id'];
}
