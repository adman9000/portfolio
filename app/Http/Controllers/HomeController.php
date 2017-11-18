<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Exchanges;

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

        $exchange = new Exchanges();

        $data['stats'] = $exchange->getAccountStats();


        //Add the GBP value to the data array
        $data['usd_gbp_rate']  = env("USD_GBP_RATE");
        $data['btc_value'] = $data['stats']['total']['btc_value'];
        $data['usd_value'] = number_format($data['stats']['total']['usd_value'], 2);
        $data['gbp_value'] = number_format(($data['stats']['total']['usd_value'] / $data['usd_gbp_rate']), 2);


        return view('home', $data);
    }
}
