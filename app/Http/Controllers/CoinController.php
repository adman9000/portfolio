<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Coin;
use App\CoinPrice;
use App\Transaction;
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
        $btc_value = 0;

         $data['usd_gbp_rate']  = env("USD_GBP_RATE");


        $data['coins'] = Coin::with('latestCoinprice')->get();





        //Get balances of my coins according to Bittrex
        $balances = Bittrex::getBalances();
/*
        //Get latest markets for everythign on bittrex - BECAUSE WE ARE NOT UP TO DATE ON DEV. TODO: REMOVE
        $markets = Bittrex::getMarketSummaries();

        foreach($data['coins'] as $c=>$coin) {

            foreach($markets['result'] as $market) {

                if($market['MarketName'] == 'BTC-'.$coin->code) {
                   $data['coins'][$c]->latestCoinprice->current_price = $market['Last'];
                }

            }

        }
*/

        foreach($data['coins'] as $c=>$coin) {

            if($data['coins'][$c]->latestCoinprice) $current_price = $data['coins'][$c]->latestCoinprice->current_price; else  $current_price = 0;

            foreach($balances['result'] as $balance) {

                if($balance['Currency'] == $coin->code) {
                    $data['coins'][$c]->balance = $balance['Balance'];
                    $data['coins'][$c]->btc_value = $data['coins'][$c]->balance * $current_price;

                    $btc_value += $data['coins'][$c]->btc_value;
                    break;
                }
            }

            //echo $coin->code." : ".$coin->latestCoinPrice->current_price." / ".$coin->buy_point." = ".($coin->latestCoinPrice->current_price/$coin->buy_point)."<br />";
            $data['coins'][$c]->diff = round((($current_price / $coin->buy_point) * 100) - 100, 2)."%";
        }

/*
         //one-off setup of existing coins on scheme
        foreach($data['coins'] as $coin) {

            if($coin->balance>0) {
                $scheme_info = [
                    'coin_id' => $coin->id,
                    'scheme_id' => 3,
                    'set_price' => $coin->buy_point,
                    'amount_held' => $coin->balance,
                    'been_bought' => 1,
                    'highest_price' => $coin->buy_point
                ];

                DB::table('coin_scheme')->insert($scheme_info);
            }
        }
*/

        $data['btc_value'] = $btc_value;

        return view('coins.index', $data);
    }


    /** TODO: recode to show selected charts together
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


    /* Quick functions to convert coins **/

    function toBTC(Coin $coin) {
        //Convert all of this coin to BTC in Bittrex
        $result = Bittrex::getBalance($coin->code);
        $volume = $result['result']['Balance'];

        $result = Bittrex::getMarketSummary("BTC-".$coin->code);
        $rate = $result['result'][0]['Last'];

        //Place order
        $order = Bittrex::sellLimit("BTC-".$coin->code, $volume, $rate);

        if(!$order['success']) {
            //Order failed, alert me somehow
            var_dump($order);
            return "Order Failed";
        }
        else {
             //Order successful, save transaction to DB
            $transaction_info = array(
                "coin_bought_id" => 0,
                "coin_sold_id" => $coin->id,
                "amount_sold" => $volume,
                "amount_bought" => $volume*$rate,
                "exchange_rate" => $rate,
                'fees' => 0,
                'user_id' => 1,
                'scheme_id' => false
                );
            Transaction::create($transaction_info);

            return "Order Successful";
        }

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
