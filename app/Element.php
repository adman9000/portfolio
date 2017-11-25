<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Element extends Model
{
    //

         //relationships
    public function page() {

    	return $this->belongsTo('App\Page');
    	
    }

    //Get the correct full url for this element
    public function url() {

    	$url = $this->page->url();

    	$url .= "/".$this->slug;

    	return $url;
    }
}
