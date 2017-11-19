<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

use Illuminate\Http\Request;
use App\Page;
use App\Element;


class WebsiteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        //If there is no url path we are on the homepage, so find it!
        if($request->path() == "/") {

            $page = Page::where("online", "=", 1)->orderBy("position","asc")->first();

        }

        else {

            //Get the slug from the url
            $url_array = explode("/", $request->path());
            $slug = $url_array[sizeof($url_array)-1];

            //All slugs are unique (across all tables). TODO: enforce this! url table??

            //First check for matching elements
            $element = Element::where("slug", "=", $slug)->first();

            if($element) {

                //Element found
                //Get the page for this element
                $page = $element->page;

                //Get the (inpage) template for this element type
                $element_template = "elements/".$element->type;

                //Add the element to the data array
                $data['element'] = $element;

                //Check we are on the correct url for this element
                if(View::exists($element_template)) $correct_url = $element->url();
                else $correct_url = $page->url();

            }
            else {
                //No element found
                $element_template = false;

                //Get page from slug
                $page = Page::where("slug", "=", $slug)->first();

                //Check we are on the correct url for this page
                if($page) $correct_url = $page->url();

            }

        }

        //Set the page template
        if($page) $template = "page_templates/".$page->template->filename;
        else $template = "404";

        //Add the page to the data array
        $data['page'] = $page;

        
        if("/".$request->path() != $correct_url) return redirect($correct_url);

        return view()->first([$element_template, $template, '404'], $data);
    }
}
