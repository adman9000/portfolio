<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminShortcut extends Model
{
    //

    public function admin() {

    	return $this->hasOne('App\User');
    	
    }
}
