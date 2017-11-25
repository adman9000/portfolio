<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    //

     //relationships
    public function page() {

    	return $this->belongsTo('Page');
    	
    }
}
