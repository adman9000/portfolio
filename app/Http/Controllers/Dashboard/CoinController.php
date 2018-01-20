<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Portfolio\Coin;
use App\Modules\Portfolio\CoinPrice;
use App\Modules\Portfolio\Transaction;
use App\Modules\Portfolio\Wallet;
use App\Modules\Portfolio\UserCoin;
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
        
        $user = Auth::user();

        //Load all this users wallets
        $wallets = Wallet::with('coin')->where("user_id", $user->id)->get();

        //Load all this users exchange coins
        $usercoins = UserCoin::with(['coin', 'exchangeCoin'])->where("user_id", $user->id)->get();

        foreach($wallets as $wallet) {

            if($wallet->gbp_value > 1.00) {

                //get prices from json
                $prices = json_decode($wallet->coin->prices, true);
                $wallet->coin->gbp_price = $prices['latest']['gbp'];

                //Price & value when bought
                $valueBoughtAt = $wallet->valueBoughtAt();
                $wallet->coin->original_gbp_price = $valueBoughtAt->gbp_price;
                $wallet->original_gbp_value = $valueBoughtAt->gbp_value;

                $wallet->value_change = round($wallet->gbp_value / $wallet->original_gbp_value * 100, 1);

                $data['coins'][] = $wallet;

            }
        }

        foreach($usercoins as $ucoin) {

            if($ucoin->gbp_value > 1.00) {
                $ucoin->exchangeCoin->load(["exchange", 'latestCoinprice']);
                $ucoin->coin->gbp_price = $ucoin->exchangeCoin->latestCoinprice->gbp_price;
                $data['coins'][] = $ucoin;
            }
        }

        return view('dashboard.coins.index', $data);
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


            return view('dashboard.coins.charts', $data);
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
     * Display the specified coin.
     * IN PROGRESS: Default display shows coinmarketcap data and list of exchanges & latest price at each
     * TODO: If logged in you also get your balance at each exchange plus cold storage
     * TODO: If you have the trade permission you get trade buttons
     * TODO: If you have the autotrade permission you get schemes this coin is in
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Coin $coin)
    {

        //Logged in user
        $user = Auth::user();

        //chart data
        //start with 1 month chart

        //Get an array of datetimes 1 hour apart
        $startTime  = new \DateTime("1 month ago");
        $timeStep   = 60;

        $startTime->setTime($startTime->format('G'),0);
        $endTime    = new \DateTime();
        $timeArray  = array();
        $high = 0;

        while($startTime <= $endTime)
        {
            $timeArray[$startTime->format('Y-m-d H:i')] = "null";
            $startTime->add(new \DateInterval('PT'.$timeStep.'M'));
        }

        $coin->load('exchanges');

        //Loop through coin prices and add them to the time array where times match
        foreach($coin->coinprices as $price) {
            $pricetime = new \DateTime($price->created_at);

            //Get nearest 5 mins
            $mins = round($pricetime->format('i')/$timeStep) * $timeStep;

            //Remove the seconds and set to nearest 5 mins so we can match to the minute
            $pricetime->setTime($pricetime->format('G'),$mins, 0);

            //If we have a time set in the price array, give it the price
            if(isset($timeArray[$pricetime->format('Y-m-d H:i')]))
                $timeArray[$pricetime->format('Y-m-d H:i')] = $price->btc_price;

            //Get the highest point of the chart
            $high = max($high, $price->btc_price);
        }
        
        if($user->can('autotrade')) {

            foreach($coin->schemes as $s=>$scheme) {
                $coin->schemes[$s]->pivot->buy_price =  $scheme->pivot->set_price - ($scheme->pivot->set_price * $scheme->buy_drop_percent/100);
                if($coin->schemes[$s]->pivot->buy_price>0) $coin->schemes[$s]->pivot->buy_amount =  $scheme->buy_amount/$coin->schemes[$s]->pivot->buy_price;
                $coin->schemes[$s]->pivot->sell_trigger_1 =  $scheme->pivot->set_price + ($scheme->pivot->set_price * $scheme->sell_1_gain_percent/100);
                $coin->schemes[$s]->pivot->sell_trigger_2 = $scheme->pivot->set_price + ($scheme->pivot->set_price * $scheme->sell_2_gain_percent/100);
                $coin->schemes[$s]->pivot->sell_point_1 = max($coin->schemes[$s]->pivot->sell_trigger_1, $coin->schemes[$s]->pivot->highest_price) - (max($scheme->pivot->set_price, $coin->schemes[$s]->pivot->highest_price) * $scheme->sell_1_drop_percent/100);
                $coin->schemes[$s]->pivot->sell_point_2 = max($coin->schemes[$s]->pivot->sell_trigger_2, $coin->schemes[$s]->pivot->highest_price) - (max($scheme->pivot->set_price, $coin->schemes[$s]->pivot->highest_price) * $scheme->sell_2_drop_percent/100);         
            }
        }

        $data['chart_highest'] = $high;
        $data['chart_data'] = $timeArray;
        $data['coin'] = $coin;
        $data['chart_show_every'] = $timeStep;

        return view("dashboard.coins.show", $data);
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
        return view("dashboard.coins.edit", array("coin" => $coin));
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

        //TODO: must be better way for editing lots of fields..use fill
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
