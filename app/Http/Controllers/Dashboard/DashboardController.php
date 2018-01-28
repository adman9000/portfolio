<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//use App\Repositories\Exchanges;
use App\Modules\Portfolio\Exchange;
use App\Modules\Portfolio\UserExchange;
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

    


    /** ajax()
     * All ajax calls go here
    **/
    public function ajax(Request $request, $view=false, $id=false) {

      switch($view) {

        case "portfolio" :

          $user = Auth::user();
          
          switch(request('period')) {
            case "24hour" :
            $user->load('userValues1Day');
            $chart_data = $user->userValues1Day;
              break;
            case "7day" :
            $user->load('userValues1Week');
            $chart_data = $user->userValues1Week;
              break;
            case "1month" :
            $user->load('userValues1Month');
            $chart_data = $user->userValues1Month;
              break;
            }

          $data = array();
          $data['chart'] = array();
          foreach($chart_data as $valuation) {
              switch (request('type')) {
                case "exchange" :
                   $data['chart'][date("d G:i", strtotime($valuation->created_at))] = $valuation->exchanges_gbp_value;
                  break;

                case "wallet" :
                   $data['chart'][date("d G:i", strtotime($valuation->created_at))] = $valuation->wallets_gbp_value;
                  break;

                default : 
                   $data['chart'][date("d G:i", strtotime($valuation->created_at))] = $valuation->gbp_value;
                  break;
              }
          }

          echo json_encode($data);;
          break;

      }

      die();

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

                //Update API keys etc for logged in user
                case "update_user_exchanges" :

                  $exchanges = Exchange::all();
                  foreach($exchanges as $exchange) {
                   
                    $data = array();
                    $keys = array();
                    $data['user_id'] = $user->id;
                    $data['exchange_id'] = $exchange->id;
                    $keys['api_key'] = request("api_key")[$exchange->id];
                    $keys['api_secret'] = request("api_secret")[$exchange->id];

                    //Delete if fields blank
                    if(!request("api_key")[$exchange->id] && !request("api_secret")[$exchange->id]) 
                      UserExchange::where($data)->delete();
                    else UserExchange::updateOrCreate($data, $keys);
                   // dd($user_exchange);
                  }

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

              case "profile" :
                return $this->profilePage();

            default :
                abort(404);
                break;

        }

    }



    //------- PAGE VIEW FUNCTIONS -------//

    /** 
     * profilePage()
     * Members profile page with form for updating details
     * @return \Illuminate\Http\Response
    **/
    public function profilePage() {

       $user = Auth::user();


       $user->load('exchanges');

       $data['exchanges'] = Exchange::all();

       $data['user'] = $user;

       //Link up users exchange data where exists
       foreach($data['exchanges'] as $i=>$exchange) {
          foreach($user->exchanges as $user_exchange) {
              if($exchange->id == $user_exchange->exchange_id) {
                $data['exchanges'][$i]->pivot = $user_exchange;
              }
          }
       }

       return view("dashboard.profile", $data);

    }


    /**
     * Show the application dashboard.
     * TODO: clean this up
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

       $assets = array();

       $user = Auth::user();

       //Are we looking at wallets, exchanges or both


      if((!request('type')) || (request('type')=="wallet")) {

        //Load all this users wallets
        $user->load('wallets');

        foreach($user->wallets as $wallet) {

          $data['btc_value'] += $wallet->btc_value;
          $data['usd_value'] += $wallet->usd_value;
          $data['gbp_value'] += $wallet->gbp_value;


          $wallet->load("coin");

          if(!isset($assets[$wallet->coin_id])) {

              $wallet1HourAgo = $wallet->value1HourAgo();
              $wallet1DayAgo = $wallet->value1DayAgo();
              $wallet1WeekAgo = $wallet->value1WeekAgo();

              $assets[$wallet->coin_id] = array();
              $assets[$wallet->coin_id]['code'] = $wallet->coin->code;
              $assets[$wallet->coin_id]['gbp_value'] =0;
              $assets[$wallet->coin_id]['balance'] =0;
              $assets[$wallet->coin_id]['gbp_value_1_hour'] =  0;
              $assets[$wallet->coin_id]['gbp_value_1_day'] =  0;
              $assets[$wallet->coin_id]['gbp_value_1_week'] =  0;
          }

           $assets[$wallet->coin_id]['balance'] += $wallet->balance;
           $assets[$wallet->coin_id]['gbp_value'] += $wallet->gbp_value;
              $assets[$wallet->coin_id]['gbp_value_1_hour'] += $wallet1HourAgo ? $wallet1HourAgo->gbp_value : 0;
              $assets[$wallet->coin_id]['gbp_value_1_day'] += $wallet1DayAgo ? $wallet1DayAgo->gbp_value : 0;
              $assets[$wallet->coin_id]['gbp_value_1_week'] += $wallet1WeekAgo ? $wallet1WeekAgo->gbp_value : 0;

        }

      }



       if((!request('type')) || (request('type')=="exchange")) {

          //Load all this users coins
          $user->load('coins');

          //Load all the coins for each of their exchanges
          foreach($user->exchanges as $exchange) {
              $exchange->exchange->load('coins');

              //Loop through users coins & calculate the current value of each in GBP & USD
              foreach($user->coins as $ucoin) {


                  foreach($exchange->exchange->coins as $ecoin) {
                      if($ecoin->id == $ucoin->exchange_coin_id) {

                          $coinprice1HourAgo = $ecoin->coinprice1HourAgo();
                          $coinprice1DayAgo = $ecoin->coinprice1DayAgo();
                          $coinprice1WeekAgo = $ecoin->coinprice1WeekAgo();

                          $data['btc_value'] += $ucoin->balance * $ecoin->btc_price;
                          $data['usd_value'] += $ucoin->balance * $ecoin->usd_price;
                          $data['gbp_value'] += $ucoin->balance * $ecoin->gbp_price;

                          if(!isset($assets[$ecoin->coin_id])) {
                              $assets[$ecoin->coin_id] = array();
                              $assets[$ecoin->coin_id]['code'] = $ecoin->code;
                              $assets[$ecoin->coin_id]['gbp_value'] =0;
                              $assets[$ecoin->coin_id]['balance'] =0;
                              $assets[$ecoin->coin_id]['gbp_value_1_hour'] =0;
                              $assets[$ecoin->coin_id]['gbp_value_1_day'] =0;
                              $assets[$ecoin->coin_id]['gbp_value_1_week'] =0;
                          }
                          $assets[$ecoin->coin_id]['balance'] += $ucoin->balance;
                          $assets[$ecoin->coin_id]['gbp_value'] += $ucoin->balance * $ecoin->gbp_price;
                          if($coinprice1HourAgo) $assets[$ecoin->coin_id]['gbp_value_1_hour'] += $ucoin->balance * $coinprice1HourAgo->gbp_price;
                          if($coinprice1DayAgo) $assets[$ecoin->coin_id]['gbp_value_1_day'] += $ucoin->balance * $coinprice1DayAgo->gbp_price;
                          if($coinprice1WeekAgo) $assets[$ecoin->coin_id]['gbp_value_1_week'] += $ucoin->balance * $coinprice1WeekAgo->gbp_price;
                      }
                  }

              }

          }

        }

        usort($assets, function($a, $b)
        {
            return $a['gbp_value'] < $b['gbp_value'];
        });

        $data['coins'] = $assets;

        $user->load('userValues1Day');

        $data['chart'] = array();
        foreach($user->userValues1Day as $valuation) {
            switch (request('type')) {
              case "exchange" :
                 $data['chart'][date("d G:i", strtotime($valuation->created_at))] = $valuation->exchanges_gbp_value;
                break;

              case "wallet" :
                 $data['chart'][date("d G:i", strtotime($valuation->created_at))] = $valuation->wallets_gbp_value;
                break;

              default : 
                 $data['chart'][date("d G:i", strtotime($valuation->created_at))] = $valuation->gbp_value;
                break;
            }
           
        }

        //Format the currency values
        $data['usd_value'] = number_format($data['usd_value'], 2);
        $data['gbp_value'] = number_format($data['gbp_value'], 2);


        return view('dashboard.home', $data);
    }

}
