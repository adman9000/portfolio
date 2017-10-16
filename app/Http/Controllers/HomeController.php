<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use adman9000\Bittrex\Bittrex;
use App\User;
use App\Coin;
use App\Scheme;
use App\Transaction;
use App\Notifications\Trade;

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

        //Get balances of my coins according to Bittrex
        $balances = Bittrex::getBalances();

        $btc_market = Bittrex::getMarketSummary("USDT-BTC");

        //Get latest markets for everythign on bittrex
        $markets = Bittrex::getMarketSummaries();

        $subtotal = 0;

        foreach($balances['result'] as $balance) {

            //include BTC
            if($balance['Currency'] == "BTC") {

                $subtotal += $balance['Balance'];
                $data['btc_balance'] =  $balance['Balance'];

            }
            else {


                foreach($markets['result'] as $market) {

                    if($market['MarketName'] == 'BTC-'.$balance['Currency']) {


                        $value = $balance['Balance'] * $market['Last'];

                        $subtotal += $value;

                        $data[$balance['Currency']] = $value;
                        break;

                    }
                }
            }
        }

        $data['num_coins'] = sizeof($balances['result']);
        $data['usd_gbp_rate']  = env("USD_GBP_RATE");
        $data['btc_value'] = $subtotal;
        $data['usd_value'] = $subtotal * $btc_market['result'][0]['Last'];
        $data['gbp_value'] = $data['usd_value'] / $data['usd_gbp_rate'];


        return view('home', $data);
    }
}
