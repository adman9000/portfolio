<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//use App\Repositories\Exchanges;
//use App\Modules\Portfolio\UserExchange;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        //Get all our account stats from the different exchanges we use

       $user = Auth::user();

       $data = array();
       $data['stats'] = array();
       $data['stats']['total'] = array();
       $data['stats']['total']['btc_value'] = 0;
       $data['stats']['total']['usd_value'] = 0;

       foreach($user->exchanges as $exchange) {

            $data['stats'][$exchange->exchange->slug] = $exchange->getAccountStats();
            $data['stats']['total']['btc_value'] += $data['stats'][$exchange->exchange->slug]['total_btc_value'];
            $data['stats']['total']['usd_value'] += $data['stats'][$exchange->exchange->slug]['total_usd_value'];

        }

        //Add the GBP value to the data array
        $data['usd_gbp_rate']  = env("USD_GBP_RATE");
        $data['btc_value'] = $data['stats']['total']['btc_value'];
        $data['usd_value'] = number_format($data['stats']['total']['usd_value'], 2);
        $data['gbp_value'] = number_format(($data['stats']['total']['usd_value'] / $data['usd_gbp_rate']), 2);


        return view('dashboard.home', $data);
    }


      /** run()
     * All non ajax calls to this controller pass though this function
     * @param  \Illuminate\Http\Request  $request
     * @param  $view
     * @return \Illuminate\Http\Response
     */
    public function run(Request $request, $view=false) {

        //First try to get a response from posted actions
        $response = $this->actions($request);

        //if there is none then get a response from the view
        if(!$response)
            $response =  $this->view($view);

        return $response;
    }


    /* actions()
     * All posted actions get processed here
    **/
    public function actions(Request $request) {

         if($request->isMethod('post')) {

            switch($request->input('action')) {


            }

        }

        return false;
    }


    /* Respond with a view
    **/
    public function view($view=false) {


        switch($view) {


            case "" :
            case "index" :
            case "home" :
                return $this->index();

            default :
                abort(404);
                break;

        }

    }

}
