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
       $data = array();
       $data['btc_value'] = 0;
       $data['usd_value'] = 0;
       $data['gbp_value'] = 0;

       $user = Auth::user();

        //Load all this users coins
        $user->load('coins');

        //Load all the coins for each of their exchanges
        foreach($user->exchanges as $exchange) {
            $exchange->exchange->load('coins');

            //Loop through users coins & calculate the current value of each in GBP & USD
            foreach($user->coins as $ucoin) {
                foreach($exchange->exchange->coins as $ecoin) {
                    if($ecoin->id == $ucoin->exchange_coin_id) {

                        $data['btc_value'] += $ucoin->balance * $ecoin->btc_price;
                        $data['usd_value'] += $ucoin->balance * $ecoin->usd_price;
                        $data['gbp_value'] += $ucoin->balance * $ecoin->gbp_price;

                    }
                }

            }

        }

        //Format the currency values
        $data['usd_value'] = number_format($data['usd_value'], 2);
        $data['gbp_value'] = number_format($data['gbp_value'], 2);


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


       $user = Auth::user();


         if($request->isMethod('post')) {

            switch($request->input('action')) {

                case "resync" :
 
                    //Update the logged in users balances from each of their exchanges
                   foreach($user->exchanges as $exchange) {
                        $exchange->updateBalances();
                    }

                break;

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
