<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    //


    //Relationships
    public function pages() {

    	return $this->hasMany('App\Page');

    }

    
}
