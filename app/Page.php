<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Content;

class Page extends Model
{
    //

    //relationships
    public function template() {

    	return $this->belongsTo('App\Template');

    }

    public function contents() {

    	return $this->hasMany('App\Content');

    }

    public function elements() {

    	return $this->hasMany('App\Element');

    }

    /** ancestors
     * Get the hierarchy of this page for breadcrumbs, urls etc
    **/
    public function ancestors($array = array()) {

        if($this->parent_id ==0) return $array;
        else {
            $array[] = $this->parent;
            return $this->parent->ancestors($array);
        }
    }

     public function parent()
    {
        return $this->belongsTo('App\Page', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\Page', 'parent_id');
    }

    /** url()
     * Return the correct url for this page
     * For redirects etc
     **/
    public function url() {

        $url = "";

        //Get all page ancestors into an array
        $ancestors = $this->ancestors();

        foreach($ancestors as $a) {

            $url .= "/".$a->slug;

        }

        //add this page to the url
        $url .= "/".$this->slug;
        
        //return the full url path
        return $url;
    }

}
