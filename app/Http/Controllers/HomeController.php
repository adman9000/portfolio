<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Exchanges;
use App\Modules\Portfolio\UserExchange;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
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


        return view('home', $data);
    }
}
