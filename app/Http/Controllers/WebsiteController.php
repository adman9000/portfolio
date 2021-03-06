<?php 
/* WebsiteController
 * Main controller for website pages & content
 * TODO: dashboard controller etc should extend this one?
 **/


namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

use Illuminate\Http\Request;
use App\Page;
use App\Element;
use App\Modules\Portfolio\Coin;


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

        $data = array();
        
        //The correct url for the page being loaded in case we need to redirect
        $correct_url = false;

        //The template for the element we are viewing
        $element_template = false;

        //The template for the page we are viewing
        $page_template = false;


        //If there is no url path we are on the homepage, so find it!
        if($request->path() == "/") {

            $page = Page::where("online", "=", 1)->orderBy("position","asc")->first();


        $btc_value = 0;

         $data['usd_gbp_rate']  = env("USD_GBP_RATE");


        $data['coins'] = Coin::with('latestCoinprice')->get();

        //GET COINS FROM CRYPTOCOMPARE
        $json = file_get_contents ("https://min-api.cryptocompare.com/data/all/coinlist");

        $result = json_decode($json, true);

        $data['coins'] = $result['Data'];

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
        else abort(404);

        //Add the page to the data array
        $data['page'] = $page;

        
        if(($correct_url) && ("/".$request->path() != $correct_url)) return redirect($correct_url);

        return view()->first([$element_template, $template, '404'], $data);
    }
}
