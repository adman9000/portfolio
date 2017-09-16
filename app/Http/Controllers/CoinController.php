<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Coin;
use App\CoinPrice;
use App\Events\PusherEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use adman9000\Bittrex\Bittrex;
use Carbon\Carbon;

class CoinController extends Controller
{
    //

    public function __construct() {
         $this->middleware('auth');
    }

     public function index()
    {
        $data = array();

        //Include latest prices & exclude Euro
        $coins = Coin::with('latestCoinprice')->where("code", "!=","EUR")->get();

        //Get transaction for this user - probably better way to do this!
        $user = Auth::user();
        //$transactions = $user->transactions;

        $amount_owned = array();

        //Use Bittrex figures, not transactions

         $balances = Bittrex::getBalances();

        $my_balances = array();
        foreach($balances['result'] as $balance) {
            foreach($coins as $c=>$coin) {
                if($coin->code == $balance['Currency']) $coins[$c]->amount_owned= $balance['Balance'];
            }
        }


        //Do we need this on every page load?
        $btc_market = Bittrex::getMarketSummary("USDT-BTC");

        $btc_balance = Bittrex::getBalance("BTC");
       
        $data['btc_additional_amount'] = $btc_balance['result']['Balance'];
        $data['btc_usd_rate'] = $btc_market['result'][0]['Last'];
        $data['usd_gbp_rate']  = env("USD_GBP_RATE");       

        $data['coins'] = $coins;


        return view('coins.index', $data);
    }


    /**
     * Show the price charts.
     *
     * @return \Illuminate\Http\Response
     */
     public function charts($time=false)
        {
            $data = array();
            //Include latest prices & exclude Euro
            if($time == "24hr") {
                $coins = Coin::with(['coinprices' => function($query) {
                     $yesterday = date("Y-m-d G:i:s", time()-(60*60*24));
                    $query->where('coin_prices.created_at', '>=', $yesterday);
                }])->where("code", "!=","EUR")->get();


                    $yesterday = new Carbon("24 hours ago");
            }
            else {
                $coins = Coin::where("code", "!=","EUR")->get();
                $yesterday = Carbon::createFromDate(2001, 01, 01);
            }
            $data['coins'] = $coins;



            $result = Bittrex::getChartData("BTC-GNT", 'hour');

            $gnt = new Coin();
            $gnt->id = 99;
            $gnt->code = "GNT";
            foreach($result['result'] as $tick) {
                $price = new CoinPrice();
                $price->created_at = new Carbon($tick['T']);

                $price->current_price = $tick['L'];
                if($price->created_at->gt($yesterday)) $gnt->coinprices[] = $price;
            }
            $data['coins'][] = $gnt;


            $result = Bittrex::getChartData("BTC-ARK", 'hour');

            $gnt = new Coin();
            $gnt->id = 100;
            $gnt->code = "ARK";
            foreach($result['result'] as $tick) {
                $price = new CoinPrice();
                $price->created_at = new Carbon($tick['T']);
                $price->current_price = $tick['L'];
                if($price->created_at->gt($yesterday)) $gnt->coinprices[] = $price;
            }
            $data['coins'][] = $gnt;

         $result = Bittrex::getChartData("BTC-OMG", 'hour');

            $gnt = new Coin();
            $gnt->id = 101;
            $gnt->code = "OMG";
            foreach($result['result'] as $tick) {
                $price = new CoinPrice();
                $price->created_at = new Carbon($tick['T']);
                $price->current_price = $tick['L'];
                if($price->created_at->gt($yesterday)) $gnt->coinprices[] = $price;
            }
            $data['coins'][] = $gnt;


            return view('coins.charts', $data);
        }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view("coins.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         //Validate input
    	$this->validate(request(), [
    		'code' => 'required|min:2|max:8',
    		'name' => 'required']
    		);

        //only safe if fillable/guarded set
 		Coin::create(request()->all());

 		return $this->index();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Coin $coin)
    {
        //
        $btc_buy_amount = env('BTC_BUY_AMOUNT');
        $sell_point_1_multiplier = env('SELL_POINT_1');
        $sell_point_2_multiplier = env('SELL_POINT_2');
        $sell_drop_2_percentage = env('SELL_DROP_2');
        
        $coin->sell_point_1 = $coin->buy_point * $sell_point_1_multiplier;
        $coin->sell_trigger_2 = $coin->buy_point * $sell_point_2_multiplier;
        $coin->sell_point_2 = $coin->sell_trigger_2 - (( $coin->highest_price*$sell_drop_2_percentage)/100);

        return view("coins.show", array("coin" => $coin));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Coin $coin)
    {
        //
        return view("coins.edit", array("coin" => $coin));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Coin $coin)
    {
        //
         //Validate input
    	$this->validate(request(), [
    		'code' => 'required|min:2|max:8',
    		'name' => 'required']
    		);

        //TODO: must be better way for editing lots of fields..
    	$coin->code = $request->code;
		$coin->name = $request->name;
		$coin->save();

 		return $this->index();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Coin $coin)
    {
        //
        $coin->delete();
 		return $this->index();
    }
}
