<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Modules\Portfolio\Coin;
use App\Modules\Portfolio\Alert;
use App\Modules\Portfolio\Wallet;
use App\Modules\Portfolio\UserCoin;

class AlertController extends Controller
{
    //

    public function __construct() {
         $this->middleware('auth');
    }

     public function index()
    {
        $data = array();
        $user = Auth::user();
        $data['alerts'] = $user->alerts;

        return view('dashboard.alerts.index', $data);
    }




    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $data = array();
        $data['coins'] = Coin::with('latestCoinprice')->get();
        return view("dashboard.alerts.create", $data);
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
    		'coin_id' => 'required',
            ]
    	);

        $data = request()->all();
        $data['user_id'] =  Auth::id();

        $coin = Coin::with('latestCoinprice')->find($data['coin_id']);

        //Get total balance in wallets and exchanges of this coin
        //TODO - move this to a function in user model?
        $wallets = Wallet::where("user_id", $data['user_id'])->where("coin_id", $coin->id)->get();
        $ucoins = UserCoin::where("user_id", $data['user_id'])->where("coin_id", $coin->id)->get();

        $balance = 0;
        foreach($wallets as $wallet) {
            $balance += $wallet->balance;
        }
        foreach($ucoins as $ucoin) {
            $balance += $ucoin->balance;
        }
        $data['balance'] = $balance;

        $gbp_price = $coin->latestCoinprice['gbp_price'];
        $data['gbp_current_price'] = $gbp_price;
        $data['gbp_current_value'] = $data['balance'] * $gbp_price;
    

        //only safe if fillable/guarded set
 		Alert::create($data);

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
    public function show(Alert $alert)
    {
        $data = ['alert' => $alert];
        return view("dashboard.alerts.show", $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Alert $alert)
    {
        //
        $data = ['alert' => $alert];
        $data['coins'] = Coin::with('latestCoinprice')->get();
        return view("dashboard.alerts.edit", $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Alert $alert)
    {
        //Validation required
		$alert->fill(request()->all());
        $alert->save();

 		return $this->index();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Alert $alert)
    {
        //
        $alert->delete();
 		return $this->index();
    }
}
